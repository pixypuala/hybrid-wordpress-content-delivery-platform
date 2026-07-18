<?php
/**
 * In-memory content source for exercising the delivery handler without WordPress.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Tests\Double;

use Pixypuala\HybridDelivery\Delivery\ContentSource;

/**
 * Serves raw rows from an in-memory map keyed by article id.
 */
final class FakeContentSource implements ContentSource {

	/**
	 * @param array<int, array<string, mixed>> $rows Rows keyed by article id.
	 */
	public function __construct( private readonly array $rows ) {}

	public function find( int $id ): ?array {
		return $this->rows[ $id ] ?? null;
	}

	public function all(): array {
		return array_values( $this->rows );
	}
}
