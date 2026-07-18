<?php
/**
 * Wall-clock implementation of the delivery Clock port.
 *
 * The default clock the route glue wires in production. Framework-free: it reads
 * the system time and normalises it to ISO-8601 UTC.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Delivery;

/**
 * Returns the real current instant in UTC.
 */
final class SystemClock implements Clock {

	/**
	 * The current instant, ISO-8601 in UTC.
	 *
	 * @return string
	 */
	public function now_iso8601(): string {
		return gmdate( 'c' );
	}
}
