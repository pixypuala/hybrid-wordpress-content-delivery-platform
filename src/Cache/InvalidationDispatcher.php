<?php
/**
 * Framework-free invalidation wiring: change event in, purge call out.
 *
 * When an article is published, updated, or removed, its cached edge
 * representations — and the listing responses that include it — must be purged.
 * This dispatcher is the invoker: on a change event it derives the surrogate tag
 * set for the article via SurrogateKeyResolver and hands that set to an injected
 * CachePurger. It knows nothing about WordPress hooks or the CDN's purge API, so
 * it is unit-testable with a fake purger. The WordPress hook registration and the
 * concrete CDN purger are the thin, environment-dependent glue around it.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Cache;

use Pixypuala\HybridDelivery\Resource\ArticleResource;

/**
 * Turns an article change event into a surrogate-tag purge.
 */
final class InvalidationDispatcher {

	/**
	 * @param SurrogateKeyResolver $resolver Derives the surrogate tag set.
	 * @param CachePurger          $purger   Executes the purge.
	 */
	public function __construct(
		private readonly SurrogateKeyResolver $resolver,
		private readonly CachePurger $purger,
	) {}

	/**
	 * Handle one change event: compute the tag set and purge it.
	 *
	 * The tag set is the same regardless of event type — publish, update, and
	 * delete all invalidate the article's own tag, its taxonomy-term tags, and
	 * the global all-articles tag so listing responses refresh. The event is
	 * forwarded to the purger for provenance, not to alter the tags. The set is
	 * returned so callers can log or assert exactly what was invalidated.
	 *
	 * @param ContentChangeEvent $event   What happened to the article.
	 * @param ArticleResource    $article The article whose caches must be purged.
	 *
	 * @return string[] The surrogate tags that were purged.
	 */
	public function dispatch( ContentChangeEvent $event, ArticleResource $article ): array {
		$tags = $this->resolver->invalidation_tags( $article );

		$this->purger->purge( $event, $tags );

		return $tags;
	}

	/**
	 * Purge an article that can no longer be read.
	 *
	 * A deleted or unpublished article still has cached representations keyed by
	 * its id, but there is no resource left to derive term tags from. Purging the
	 * id and the all-articles tag is the safe subset: it may leave a term listing
	 * warm for one cycle, but it never serves a deleted article. Skipping the
	 * purge entirely would do exactly that.
	 *
	 * @param ContentChangeEvent $event What happened to the article.
	 * @param int                $id    Positive article id.
	 *
	 * @return string[] The surrogate tags that were purged.
	 *
	 * @throws \InvalidArgumentException When id is not positive.
	 */
	public function dispatch_by_id( ContentChangeEvent $event, int $id ): array {
		$tags = array(
			$this->resolver->article_tag( $id ),
			SurrogateKeyResolver::ALL_ARTICLES_TAG,
		);

		$this->purger->purge( $event, $tags );

		return $tags;
	}
}
