<?php
/**
 * Translates WordPress post lifecycle events into cache invalidations.
 *
 * This is the last mile of the invalidation path: WordPress knows a post
 * changed, the dispatcher knows which surrogate tags that invalidates, and this
 * subscriber is the only thing that has to know both. It maps the post-status
 * transition onto a ContentChangeEvent, loads the article through the same
 * ContentSource and transformer the delivery route uses — so the tags derive
 * from the published contract, not from a second reading of the post — and asks
 * the dispatcher to purge. Every decision it delegates to is unit-tested; the
 * hook registration itself is the irreducible WordPress glue.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Cache;

use Pixypuala\HybridDelivery\Delivery\ContentSource;
use Pixypuala\HybridDelivery\Transform\ArticleTransformer;
use Pixypuala\HybridDelivery\Transform\TransformException;

/**
 * Binds article invalidation onto WordPress post events.
 */
final class PostChangeSubscriber {

	/**
	 * @param InvalidationDispatcher $dispatcher  Computes and executes the purge.
	 * @param ContentSource          $source      Reads the article being invalidated.
	 * @param ArticleTransformer     $transformer Produces the contract resource.
	 * @param string                 $post_type   Post type the delivery API serves.
	 */
	public function __construct(
		private readonly InvalidationDispatcher $dispatcher,
		private readonly ContentSource $source,
		private readonly ArticleTransformer $transformer,
		private readonly string $post_type = 'post',
	) {}

	/**
	 * Subscribe to the post events that change what the API returns.
	 *
	 * Guarded so the class is safe to autoload outside WordPress.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! function_exists( 'add_action' ) ) {
			return;
		}

		add_action( 'transition_post_status', array( $this, 'on_transition' ), 10, 3 );
		add_action( 'before_delete_post', array( $this, 'on_delete' ), 10, 2 );
	}

	/**
	 * Purge on publish and on edits to an already-published article.
	 *
	 * @param string    $new_status Status the post moved to.
	 * @param string    $old_status Status the post moved from.
	 * @param \WP_Post  $post       The post that changed.
	 *
	 * @return void
	 */
	public function on_transition( string $new_status, string $old_status, \WP_Post $post ): void {
		if ( $this->post_type !== $post->post_type ) {
			return;
		}

		// Only transitions that change what a consumer can read matter. A draft
		// saved as a draft was never in any cache.
		if ( 'publish' !== $new_status && 'publish' !== $old_status ) {
			return;
		}

		if ( 'publish' === $new_status ) {
			$event = 'publish' === $old_status ? ContentChangeEvent::Updated : ContentChangeEvent::Published;
		} else {
			// Unpublished: gone from the API, so its cached copies must go too.
			$event = ContentChangeEvent::Deleted;
		}

		$this->purge( (int) $post->ID, $event );
	}

	/**
	 * Purge when an article is deleted outright.
	 *
	 * @param int      $post_id Post being deleted.
	 * @param \WP_Post $post    The post object.
	 *
	 * @return void
	 */
	public function on_delete( int $post_id, \WP_Post $post ): void {
		if ( $this->post_type !== $post->post_type ) {
			return;
		}

		$this->purge( $post_id, ContentChangeEvent::Deleted );
	}

	/**
	 * Resolve the article and dispatch its invalidation.
	 *
	 * A post that no longer resolves — deleted, or malformed against the
	 * contract — still has cached copies keyed by its id, so it falls back to
	 * invalidating that id rather than skipping the purge and serving stale
	 * content.
	 *
	 * @param int                $post_id Article id.
	 * @param ContentChangeEvent $event   What happened to it.
	 *
	 * @return string[] The tags that were purged.
	 */
	private function purge( int $post_id, ContentChangeEvent $event ): array {
		$raw = $this->source->find( $post_id );

		if ( null !== $raw ) {
			try {
				return $this->dispatcher->dispatch( $event, $this->transformer->transform( $raw ) );
			} catch ( TransformException $error ) {
				unset( $error ); // Fall through to the id-only purge below.
			}
		}

		return $this->dispatcher->dispatch_by_id( $event, $post_id );
	}
}
