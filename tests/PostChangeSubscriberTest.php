<?php
/**
 * Tests the mapping from WordPress post events to cache invalidations.
 *
 * The dispatcher and resolver are tested separately; what matters here is the
 * decision the subscriber makes on its own — which transitions invalidate
 * anything at all, which ContentChangeEvent each one is, and what happens when
 * the changed article can no longer be read.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Tests;

use PHPUnit\Framework\TestCase;
use Pixypuala\HybridDelivery\Cache\ContentChangeEvent;
use Pixypuala\HybridDelivery\Cache\InvalidationDispatcher;
use Pixypuala\HybridDelivery\Cache\PostChangeSubscriber;
use Pixypuala\HybridDelivery\Cache\SurrogateKeyResolver;
use Pixypuala\HybridDelivery\Tests\Double\FakeCachePurger;
use Pixypuala\HybridDelivery\Tests\Double\FakeContentSource;
use Pixypuala\HybridDelivery\Transform\ArticleTransformer;

require_once __DIR__ . '/Double/wp-post-double.php';

final class PostChangeSubscriberTest extends TestCase {

	private FakeCachePurger $purger;

	/**
	 * Build a subscriber over a source holding the given rows.
	 *
	 * @param array<int, array<string, mixed>> $rows Rows keyed by article id.
	 *
	 * @return PostChangeSubscriber
	 */
	private function subscriber( array $rows ): PostChangeSubscriber {
		$this->purger = new FakeCachePurger();

		return new PostChangeSubscriber(
			new InvalidationDispatcher( new SurrogateKeyResolver(), $this->purger ),
			new FakeContentSource( $rows ),
			new ArticleTransformer()
		);
	}

	/**
	 * One readable article row.
	 *
	 * @param int $id Article id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function rows( int $id = 42 ): array {
		return array(
			$id => array(
				'id'           => $id,
				'slug'         => 'hello-world',
				'title'        => 'Hello World',
				'published_at' => '2026-07-18T09:30:00+00:00',
				'tags'         => array( 'news' ),
			),
		);
	}

	public function test_first_publish_dispatches_a_published_event(): void {
		$subscriber = $this->subscriber( $this->rows() );

		$subscriber->on_transition( 'publish', 'draft', new \WP_Post( 42, 'post' ) );

		$this->assertSame( ContentChangeEvent::Published, $this->purger->calls[0]['event'] );
		$this->assertSame(
			array( 'hdp:article:42', 'hdp:term:news', 'hdp:articles' ),
			$this->purger->calls[0]['tags']
		);
	}

	public function test_editing_a_published_article_dispatches_an_updated_event(): void {
		$subscriber = $this->subscriber( $this->rows() );

		$subscriber->on_transition( 'publish', 'publish', new \WP_Post( 42, 'post' ) );

		$this->assertSame( ContentChangeEvent::Updated, $this->purger->calls[0]['event'] );
	}

	/**
	 * Unpublishing removes the article from the API, so its caches must clear.
	 */
	public function test_unpublishing_dispatches_a_deleted_event(): void {
		$subscriber = $this->subscriber( $this->rows() );

		$subscriber->on_transition( 'draft', 'publish', new \WP_Post( 42, 'post' ) );

		$this->assertSame( ContentChangeEvent::Deleted, $this->purger->calls[0]['event'] );
	}

	/**
	 * A draft that was never public was never cached — purging it is pure noise.
	 */
	public function test_draft_to_draft_purges_nothing(): void {
		$subscriber = $this->subscriber( $this->rows() );

		$subscriber->on_transition( 'draft', 'draft', new \WP_Post( 42, 'post' ) );

		$this->assertSame( array(), $this->purger->calls );
	}

	public function test_another_post_type_is_ignored(): void {
		$subscriber = $this->subscriber( $this->rows() );

		$subscriber->on_transition( 'publish', 'draft', new \WP_Post( 42, 'page' ) );
		$subscriber->on_delete( 42, new \WP_Post( 42, 'page' ) );

		$this->assertSame( array(), $this->purger->calls );
	}

	/**
	 * An article that can no longer be read still has cached copies keyed by id,
	 * so it falls back to the id-only purge rather than skipping it.
	 */
	public function test_unreadable_article_falls_back_to_an_id_only_purge(): void {
		$subscriber = $this->subscriber( array() );

		$subscriber->on_delete( 42, new \WP_Post( 42, 'post' ) );

		$this->assertSame( ContentChangeEvent::Deleted, $this->purger->calls[0]['event'] );
		$this->assertSame( array( 'hdp:article:42', 'hdp:articles' ), $this->purger->calls[0]['tags'] );
	}

	/**
	 * A row that violates the contract must not silence the purge either.
	 */
	public function test_malformed_article_still_purges_by_id(): void {
		$subscriber = $this->subscriber( array( 42 => array( 'id' => 42 ) ) );

		$subscriber->on_transition( 'publish', 'publish', new \WP_Post( 42, 'post' ) );

		$this->assertSame( array( 'hdp:article:42', 'hdp:articles' ), $this->purger->calls[0]['tags'] );
	}
}
