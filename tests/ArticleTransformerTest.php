<?php
/**
 * Tests for the article content contract transformer.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Tests;

use PHPUnit\Framework\TestCase;
use Pixypuala\HybridDelivery\Transform\ArticleTransformer;
use Pixypuala\HybridDelivery\Transform\TransformException;

final class ArticleTransformerTest extends TestCase {

	private ArticleTransformer $transformer;

	protected function setUp(): void {
		$this->transformer = new ArticleTransformer();
	}

	private function validRow(): array {
		return array(
			'id'           => 42,
			'slug'         => 'hello-world',
			'title'        => 'Hello World',
			'excerpt'      => 'A short intro.',
			'html'         => '<p>Body.</p>',
			'published_at' => '2026-07-18 09:30:00',
			'author'       => 'Ada',
			'tags'         => array( 'news', 'release' ),
		);
	}

	public function test_valid_row_maps_to_contract_fields(): void {
		$article = $this->transformer->transform( $this->validRow() );
		$out     = $article->to_array();

		$this->assertSame( 42, $out['id'] );
		$this->assertSame( 'hello-world', $out['slug'] );
		$this->assertSame( array( 'news', 'release' ), $out['tags'] );
		// Timestamp is normalised to ISO-8601 UTC regardless of input format.
		$this->assertSame( '2026-07-18T09:30:00+00:00', $out['publishedAt'] );
	}

	public function test_unix_timestamp_is_normalised(): void {
		$row                 = $this->validRow();
		$row['published_at'] = 1_752_831_000; // Fixed epoch seconds.
		$out                 = $this->transformer->transform( $row )->to_array();
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}T/', $out['publishedAt'] );
	}

	public function test_missing_title_is_rejected(): void {
		$row = $this->validRow();
		unset( $row['title'] );
		$this->expectException( TransformException::class );
		$this->transformer->transform( $row );
	}

	public function test_non_positive_id_is_rejected(): void {
		$row       = $this->validRow();
		$row['id'] = 0;
		$this->expectException( TransformException::class );
		$this->transformer->transform( $row );
	}

	public function test_unparseable_date_is_rejected(): void {
		$row                 = $this->validRow();
		$row['published_at'] = 'not-a-date';
		$this->expectException( TransformException::class );
		$this->transformer->transform( $row );
	}

	public function test_dirty_tags_are_cleaned(): void {
		$row         = $this->validRow();
		$row['tags'] = array( 'news', '', '  ', 123, 'release' );
		$out         = $this->transformer->transform( $row )->to_array();
		$this->assertSame( array( 'news', 'release' ), $out['tags'] );
	}

	public function test_optional_fields_default_to_empty_string(): void {
		$row = $this->validRow();
		unset( $row['excerpt'], $row['html'], $row['author'] );
		$out = $this->transformer->transform( $row )->to_array();
		$this->assertSame( '', $out['excerpt'] );
		$this->assertSame( '', $out['html'] );
		$this->assertSame( '', $out['author'] );
	}

	public function test_transform_all_preserves_order(): void {
		$rows       = array(
			$this->validRow(),
			array_merge(
				$this->validRow(),
				array(
					'id'   => 7,
					'slug' => 'second',
				)
			),
		);
		$collection = $this->transformer->transform_all( $rows );
		$this->assertCount( 2, $collection );
		$this->assertSame( 42, $collection[0]->id );
		$this->assertSame( 7, $collection[1]->id );
	}
}
