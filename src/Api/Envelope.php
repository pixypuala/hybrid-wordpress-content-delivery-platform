<?php
/**
 * The versioned response envelope for the content API.
 *
 * Every headless response carries its data plus meta that makes it cacheable and
 * debuggable: the contract version consumers negotiated, when it was generated,
 * and provenance. Wrapping responses consistently is what lets a CDN and a React
 * client reason about freshness and compatibility.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Api;

/**
 * Wraps API payloads with contract metadata.
 */
final class Envelope {

	/** The current content-contract version. Bump on any breaking field change. */
	public const CONTRACT_VERSION = 1;

	/**
	 * @param array<string, mixed>|array<int, mixed> $data       Serialised resource(s).
	 * @param string                                 $generated_at ISO-8601 UTC.
	 * @param array<string, mixed>                   $meta       Extra meta (e.g. pagination).
	 */
	public function __construct(
		private readonly array $data,
		private readonly string $generated_at,
		private readonly array $meta = array(),
	) {}

	/**
	 * Serialisable envelope.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return array(
			'meta' => array_merge(
				array(
					'contractVersion' => self::CONTRACT_VERSION,
					'generatedAt'     => $this->generated_at,
				),
				$this->meta
			),
			'data' => $this->data,
		);
	}

	/**
	 * Encode as JSON for a response body.
	 *
	 * @return string
	 */
	public function to_json(): string {
		return (string) json_encode( $this->to_array(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}
}
