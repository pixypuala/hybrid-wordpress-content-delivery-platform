<?php
/**
 * Time port for stamping the envelope's generatedAt field.
 *
 * Injecting the clock keeps the delivery handler deterministic under test: the
 * real implementation reads the wall clock, the test implementation returns a
 * fixed instant, and the handler cannot tell the difference.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Delivery;

/**
 * Supplies the current instant as an ISO-8601 UTC string.
 */
interface Clock {

	/**
	 * The current instant, ISO-8601 in UTC.
	 *
	 * @return string e.g. "2026-07-18T09:30:00+00:00".
	 */
	public function now_iso8601(): string;
}
