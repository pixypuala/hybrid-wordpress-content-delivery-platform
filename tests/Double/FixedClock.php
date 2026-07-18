<?php
/**
 * Deterministic clock for delivery-handler tests.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Tests\Double;

use Pixypuala\HybridDelivery\Delivery\Clock;

/**
 * Returns a fixed instant so envelope output is assertable.
 */
final class FixedClock implements Clock {

	/**
	 * @param string $instant ISO-8601 UTC instant to return.
	 */
	public function __construct( private readonly string $instant ) {}

	public function now_iso8601(): string {
		return $this->instant;
	}
}
