<?php
/**
 * Read port for raw article rows the delivery handler serves.
 *
 * The handler is framework-free: it never touches WordPress directly. Instead it
 * asks a ContentSource for raw source rows and runs them through the transformer.
 * WordPress supplies a concrete implementation (the thin route glue); tests supply
 * a fake. This is the seam that keeps the delivery logic unit-testable.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Delivery;

/**
 * Supplies raw article rows to the delivery handler.
 */
interface ContentSource {

	/**
	 * One raw article row by id, or null when it does not exist.
	 *
	 * @param int $id Positive article id.
	 *
	 * @return array<string, mixed>|null Raw row in the transformer's expected shape.
	 */
	public function find( int $id ): ?array;

	/**
	 * Every published article row, in the order they should be listed.
	 *
	 * @return array<int, array<string, mixed>> Raw rows.
	 */
	public function all(): array;
}
