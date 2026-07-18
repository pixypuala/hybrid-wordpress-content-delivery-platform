<?php
/**
 * Tests for the cache-key and surrogate-tag resolver.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Tests;

use PHPUnit\Framework\TestCase;
use Pixypuala\HybridDelivery\Cache\SurrogateKeyResolver;
use Pixypuala\HybridDelivery\Resource\ArticleResource;

final class SurrogateKeyResolverTest extends TestCase {

	private SurrogateKeyResolver $resolver;

	protected function setUp(): void {
		$this->resolver = new SurrogateKeyResolver();
	}

	private function article( int $id, array $tags ): ArticleResource {
		return new ArticleResource(
			id: $id,
			slug: 'hello-world',
			title: 'Hello World',
			excerpt: '',
			html: '',
			published_at: '2026-07-18T09:30:00+00:00',
			author: 'Ada',
			tags: $tags,
		);
	}

	public function test_cache_key_is_deterministic(): void {
		$this->assertSame(
			$this->resolver->cache_key( 42, 1 ),
			$this->resolver->cache_key( 42, 1 )
		);
	}

	public function test_cache_key_varies_by_version(): void {
		$this->assertNotSame(
			$this->resolver->cache_key( 42, 1 ),
			$this->resolver->cache_key( 42, 2 )
		);
	}

	public function test_cache_key_varies_by_id(): void {
		$this->assertNotSame(
			$this->resolver->cache_key( 42, 1 ),
			$this->resolver->cache_key( 43, 1 )
		);
	}

	public function test_cache_key_rejects_non_positive_id(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->resolver->cache_key( 0, 1 );
	}

	public function test_cache_key_rejects_non_positive_version(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->resolver->cache_key( 42, 0 );
	}

	public function test_invalidation_tags_include_article_taxonomy_and_global(): void {
		$tags = $this->resolver->invalidation_tags(
			$this->article( 42, array( 'news', 'release' ) )
		);

		$this->assertContains( 'hdp:article:42', $tags );
		$this->assertContains( 'hdp:term:news', $tags );
		$this->assertContains( 'hdp:term:release', $tags );
		$this->assertContains( SurrogateKeyResolver::ALL_ARTICLES_TAG, $tags );
	}

	public function test_invalidation_tags_are_unique(): void {
		$tags = $this->resolver->invalidation_tags(
			$this->article( 42, array( 'news', 'news' ) )
		);
		$this->assertSame( array_values( array_unique( $tags ) ), $tags );
	}

	public function test_invalidation_tags_are_stably_ordered(): void {
		$tags = $this->resolver->invalidation_tags(
			$this->article( 42, array( 'news', 'release' ) )
		);
		$this->assertSame(
			array( 'hdp:article:42', 'hdp:term:news', 'hdp:term:release', 'hdp:articles' ),
			$tags
		);
	}

	public function test_invalidation_tags_skip_empty_slugs(): void {
		$tags = $this->resolver->invalidation_tags(
			$this->article( 42, array( 'news', '  ', '' ) )
		);
		$this->assertSame(
			array( 'hdp:article:42', 'hdp:term:news', 'hdp:articles' ),
			$tags
		);
	}

	public function test_article_tag_is_version_independent(): void {
		$this->assertSame( 'hdp:article:42', $this->resolver->article_tag( 42 ) );
	}
}
