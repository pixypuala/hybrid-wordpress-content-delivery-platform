<?php
/**
 * WordPress-backed ContentSource: real posts in, raw transformer rows out.
 *
 * This is the one piece of the delivery path that genuinely needs a live
 * WordPress: it reads published posts through the core API and flattens them
 * into the plain arrays ArticleTransformer expects. It holds no decisions of its
 * own — every rule about the public shape lives in the framework-free
 * transformer, which is unit-tested without WordPress. Proof for this class is
 * the live REST check documented in docs/RUNTIME-VERIFICATION.md.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Delivery;

/**
 * Reads published posts from a live WordPress install.
 */
final class WpContentSource implements ContentSource {

	/**
	 * @param string $post_type Post type to serve.
	 * @param int    $limit     Maximum rows returned by all().
	 */
	public function __construct(
		private readonly string $post_type = 'post',
		private readonly int $limit = 100,
	) {}

	/**
	 * One published post by id.
	 *
	 * @param int $id Positive post id.
	 *
	 * @return array<string, mixed>|null
	 */
	public function find( int $id ): ?array {
		$post = get_post( $id );

		if ( ! $post instanceof \WP_Post || 'publish' !== $post->post_status || $this->post_type !== $post->post_type ) {
			return null;
		}

		return $this->to_row( $post );
	}

	/**
	 * Every published post, newest first.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function all(): array {
		$posts = get_posts(
			array(
				'post_type'        => $this->post_type,
				'post_status'      => 'publish',
				'numberposts'      => $this->limit,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'suppress_filters' => false,
			)
		);

		return array_map( array( $this, 'to_row' ), $posts );
	}

	/**
	 * Flatten a post into the transformer's expected row shape.
	 *
	 * @param \WP_Post $post Published post.
	 *
	 * @return array<string, mixed>
	 */
	private function to_row( \WP_Post $post ): array {
		$tags = get_the_terms( $post, 'post_tag' );

		return array(
			'id'           => (int) $post->ID,
			'slug'         => (string) $post->post_name,
			'title'        => wp_strip_all_tags( get_the_title( $post ) ),
			'excerpt'      => wp_strip_all_tags( get_the_excerpt( $post ) ),
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Applying WordPress' own content filter, not defining a hook.
			'html'         => apply_filters( 'the_content', $post->post_content ),
			'published_at' => (string) get_post_time( 'c', true, $post ),
			'author'       => (string) get_the_author_meta( 'display_name', (int) $post->post_author ),
			'tags'         => is_array( $tags ) ? wp_list_pluck( $tags, 'slug' ) : array(),
		);
	}
}
