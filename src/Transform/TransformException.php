<?php
/**
 * Raised when raw content cannot be transformed into a valid resource.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Transform;

/**
 * Invalid or incomplete source content at the contract boundary.
 */
final class TransformException extends \RuntimeException {}
