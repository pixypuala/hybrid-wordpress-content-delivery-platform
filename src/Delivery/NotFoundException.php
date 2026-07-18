<?php
/**
 * Raised when a delivery request targets an article that does not exist.
 *
 * Framework-free: the handler throws this so the transport glue can map it to a
 * 404 without the domain layer knowing anything about HTTP.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Delivery;

/**
 * No article matches the requested id.
 */
final class NotFoundException extends \RuntimeException {}
