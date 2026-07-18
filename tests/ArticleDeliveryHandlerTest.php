<?php
/**
 * Tests for the framework-free article delivery handler.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Tests;

use PHPUnit\Framework\TestCase;
use Pixypuala\HybridDelivery\Api\Envelope;
use Pixypuala\HybridDelivery\Delivery\ArticleDeliveryHandler;
use Pixypuala\HybridDelivery\Delivery\NotFoundException;
use Pixypuala\HybridDelivery\Tests\Double\FakeContentSource;
use Pixypuala\HybridDelivery\Tests\Double\FixedClock;
use Pixypuala\HybridDelivery\Transform\ArticleTransformer;
use Pixypuala\HybridDelivery\Transform\TransformException;

final class ArticleDeliveryHandlerTest extends TestCase {

	private const GENERATED_AT = '2026-07-18T00:00:00+00:00';

	private function row( int $id, string $slug = 'hello-world' ): array {
		return array(
			'id'           => $id,
			'slug'         => $slug,
			'title'        => 'Hello World',
			'excerpt'      => 'Intro.',
			'html'         => '<p>Body.</p>',
			'published_at' => '2026-07-18 09:30:00',
			'author'       => 'Ada',
			'tags'         => array( 'news' ),
		);
	}

	private function handler( FakeContentSource $source ): ArticleDeliveryHandler {
		return new ArticleDeliveryHandler(
			$source,
			new ArticleTransformer(),
			new FixedClock( self::GENERATED_AT ),
		);
	}

	public function test_single_article_is_wrapped_in_a_versioned_envelope(): void {
		$source = new FakeContentSource( array( 42 => $this->row( 42 ) ) );
		$out    = $this->handler( $source )->article( 42 )->to_array();

		$this->assertSame( Envelope::CONTRACT_VERSION, $out['meta']['contractVersion'] );
		$this->assertSame( self::GENERATED_AT, $out['meta']['generatedAt'] );
		$this->assertSame( 42, $out['data']['id'] );
		$this->assertSame( 'hello-world', $out['data']['slug'] );
	}

	public function test_missing_article_throws_not_found(): void {
		$source = new FakeContentSource( array() );
		$this->expectException( NotFoundException::class );
		$this->handler( $source )->article( 999 );
	}

	public function test_malformed_row_surfaces_transform_exception(): void {
		$row = $this->row( 7 );
		unset( $row['title'] );
		$source = new FakeContentSource( array( 7 => $row ) );

		$this->expectException( TransformException::class );
		$this->handler( $source )->article( 7 );
	}

	public function test_collection_carries_total_and_preserves_order(): void {
		$source = new FakeContentSource(
			array(
				42 => $this->row( 42, 'first' ),
				7  => $this->row( 7, 'second' ),
			)
		);
		$out    = $this->handler( $source )->collection()->to_array();

		$this->assertSame( 2, $out['meta']['total'] );
		$this->assertCount( 2, $out['data'] );
		$this->assertSame( 'first', $out['data'][0]['slug'] );
		$this->assertSame( 'second', $out['data'][1]['slug'] );
	}

	public function test_empty_collection_reports_zero_total(): void {
		$out = $this->handler( new FakeContentSource( array() ) )->collection()->to_array();

		$this->assertSame( 0, $out['meta']['total'] );
		$this->assertSame( array(), $out['data'] );
	}
}
