<?php
/**
 * Resolves cache keys and surrogate (invalidation) tags for headless delivery.
 *
 * A headless CDN edge needs two related-but-distinct strings for every article
 * response: a *cache key* that identifies the exact cached representation, and a
 * set of *surrogate tags* attached to that representation so a purge can target
 * everything affected when the article changes. This resolver derives both
 * deterministically from an article identifier, its contract version, and its
 * taxonomy — the same inputs always yield the same key and tag set, which is
 * what makes edge invalidation reproducible instead of guesswork.
 *
 * Framework-free and fully unit-tested; the caller supplies ids and resources,
 * WordPress is not involved.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Cache;

use Pixypuala\HybridDelivery\Resource\ArticleResource;

/**
 * Derives deterministic cache keys and invalidation tag sets.
 */
final class SurrogateKeyResolver {

	/** Prefix on every key and tag, so this platform's entries never collide with another's. */
	public const NAMESPACE = 'hdp';

	/** Tag that every article response carries, for whole-collection purges. */
	public const ALL_ARTICLES_TAG = self::NAMESPACE . ':articles';

	/**
	 * Deterministic cache key for one article at a given contract version.
	 *
	 * The version is part of the key so that a contract bump serves a fresh
	 * representation rather than a stale one cached under the old shape.
	 *
	 * @param int $id      Positive article id.
	 * @param int $version Contract version the representation was built against.
	 *
	 * @return string e.g. "hdp:v1:article:42".
	 *
	 * @throws \InvalidArgumentException When id or version is not positive.
	 */
	public function cache_key( int $id, int $version ): string {
		$this->guard_positive( $id, 'id' );
		$this->guard_positive( $version, 'version' );

		return sprintf( '%s:v%d:article:%d', self::NAMESPACE, $version, $id );
	}

	/**
	 * Surrogate tag identifying a single article across all its cached forms.
	 *
	 * Unlike the cache key, this tag is version-independent: purging it clears
	 * every representation of the article regardless of the contract version it
	 * was cached under.
	 *
	 * @param int $id Positive article id.
	 *
	 * @return string e.g. "hdp:article:42".
	 *
	 * @throws \InvalidArgumentException When id is not positive.
	 */
	public function article_tag( int $id ): string {
		$this->guard_positive( $id, 'id' );

		return sprintf( '%s:article:%d', self::NAMESPACE, $id );
	}

	/**
	 * Surrogate tag for a taxonomy term (tag slug) an article belongs to.
	 *
	 * @param string $slug Non-empty term slug.
	 *
	 * @return string e.g. "hdp:term:news".
	 *
	 * @throws \InvalidArgumentException When the slug is empty.
	 */
	public function term_tag( string $slug ): string {
		$normalised = trim( $slug );
		if ( '' === $normalised ) {
			throw new \InvalidArgumentException( 'Term slug must be a non-empty string.' );
		}

		return sprintf( '%s:term:%s', self::NAMESPACE, $normalised );
	}

	/**
	 * The full set of surrogate tags to purge when an article changes.
	 *
	 * The set is: the article's own tag, one tag per taxonomy term it carries,
	 * and the global all-articles tag (so collection/listing responses are
	 * invalidated too). The result is de-duplicated and returned in a stable
	 * order — article tag first, term tags in source order, all-articles last —
	 * so callers and tests can rely on it.
	 *
	 * @param ArticleResource $article Article whose change triggers the purge.
	 *
	 * @return string[] Unique, ordered surrogate tags.
	 */
	public function invalidation_tags( ArticleResource $article ): array {
		$tags = array( $this->article_tag( $article->id ) );

		foreach ( $article->tags as $slug ) {
			if ( is_string( $slug ) && '' !== trim( $slug ) ) {
				$tags[] = $this->term_tag( $slug );
			}
		}

		$tags[] = self::ALL_ARTICLES_TAG;

		return array_values( array_unique( $tags ) );
	}

	/**
	 * @param int    $value Candidate value.
	 * @param string $label Field name for the error message.
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException When the value is not positive.
	 */
	private function guard_positive( int $value, string $label ): void {
		if ( $value <= 0 ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Framework-free domain: this message is caught at the WordPress boundary and never reaches a response.
			throw new \InvalidArgumentException( sprintf( 'Article %s must be a positive integer.', $label ) );
		}
	}
}
