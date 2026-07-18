<?php
/**
 * Tests for the versioned API envelope.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Tests;

use PHPUnit\Framework\TestCase;
use Pixypuala\HybridDelivery\Api\Envelope;
use Pixypuala\HybridDelivery\Transform\ArticleTransformer;

final class EnvelopeTest extends TestCase {

	public function test_envelope_carries_contract_version_and_generated_at(): void {
		$envelope = new Envelope( array( 'ok' => true ), '2026-07-18T00:00:00+00:00' );
		$out      = $envelope->to_array();

		$this->assertSame( Envelope::CONTRACT_VERSION, $out['meta']['contractVersion'] );
		$this->assertSame( '2026-07-18T00:00:00+00:00', $out['meta']['generatedAt'] );
		$this->assertSame( array( 'ok' => true ), $out['data'] );
	}

	public function test_extra_meta_is_merged(): void {
		$envelope = new Envelope( array(), '2026-07-18T00:00:00+00:00', array( 'total' => 3 ) );
		$this->assertSame( 3, $envelope->to_array()['meta']['total'] );
	}

	public function test_full_pipeline_produces_valid_json(): void {
		$article  = ( new ArticleTransformer() )->transform(
			array(
				'id'           => 1,
				'slug'         => 's',
				'title'        => 'T',
				'published_at' => '2026-01-01',
			)
		);
		$envelope = new Envelope( $article->to_array(), '2026-07-18T00:00:00+00:00' );

		$decoded = json_decode( $envelope->to_json(), true );
		$this->assertSame( JSON_ERROR_NONE, json_last_error() );
		$this->assertSame( 1, $decoded['data']['id'] );
		$this->assertSame( 1, $decoded['meta']['contractVersion'] );
	}
}
