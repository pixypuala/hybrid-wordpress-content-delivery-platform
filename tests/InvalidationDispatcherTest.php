<?php
/**
 * Tests for the framework-free invalidation dispatcher.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Tests;

use PHPUnit\Framework\TestCase;
use Pixypuala\HybridDelivery\Cache\ContentChangeEvent;
use Pixypuala\HybridDelivery\Cache\InvalidationDispatcher;
use Pixypuala\HybridDelivery\Cache\SurrogateKeyResolver;
use Pixypuala\HybridDelivery\Resource\ArticleResource;
use Pixypuala\HybridDelivery\Tests\Double\FakeCachePurger;

final class InvalidationDispatcherTest extends TestCase {

	private FakeCachePurger $purger;
	private InvalidationDispatcher $dispatcher;

	protected function setUp(): void {
		$this->purger     = new FakeCachePurger();
		$this->dispatcher = new InvalidationDispatcher( new SurrogateKeyResolver(), $this->purger );
	}

	private function article( int $id, array $tags = array( 'news', 'release' ) ): ArticleResource {
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

	public function test_dispatch_purges_article_taxonomy_and_global_tags(): void {
		$tags = $this->dispatcher->dispatch( ContentChangeEvent::Updated, $this->article( 42 ) );

		$expected = array( 'hdp:article:42', 'hdp:term:news', 'hdp:term:release', 'hdp:articles' );
		$this->assertSame( $expected, $tags );
		$this->assertSame( $expected, $this->purger->calls[0]['tags'] );
	}

	public function test_dispatch_calls_the_purger_exactly_once(): void {
		$this->dispatcher->dispatch( ContentChangeEvent::Published, $this->article( 42 ) );
		$this->assertCount( 1, $this->purger->calls );
	}

	/**
	 * @dataProvider event_provider
	 */
	public function test_every_event_forwards_its_provenance_and_the_full_tag_set(
		ContentChangeEvent $event
	): void {
		$tags = $this->dispatcher->dispatch( $event, $this->article( 42 ) );

		$this->assertSame( $event, $this->purger->calls[0]['event'] );
		$this->assertSame(
			array( 'hdp:article:42', 'hdp:term:news', 'hdp:term:release', 'hdp:articles' ),
			$tags
		);
	}

	/**
	 * @return array<string, array{0: ContentChangeEvent}>
	 */
	public static function event_provider(): array {
		return array(
			'publish' => array( ContentChangeEvent::Published ),
			'update'  => array( ContentChangeEvent::Updated ),
			'delete'  => array( ContentChangeEvent::Deleted ),
		);
	}

	public function test_delete_still_purges_the_last_known_taxonomy_tags(): void {
		$tags = $this->dispatcher->dispatch(
			ContentChangeEvent::Deleted,
			$this->article( 7, array( 'obituary' ) )
		);

		$this->assertSame(
			array( 'hdp:article:7', 'hdp:term:obituary', 'hdp:articles' ),
			$tags
		);
		$this->assertSame( ContentChangeEvent::Deleted, $this->purger->calls[0]['event'] );
	}
}
