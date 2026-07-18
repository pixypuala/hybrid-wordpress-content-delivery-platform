<?php
/**
 * The editorial lifecycle events that trigger cache invalidation.
 *
 * WordPress fires distinct hooks for a post being published, updated, or removed.
 * The invalidation dispatcher accepts this enum rather than raw hook strings, so
 * the framework-free wiring records *why* a purge happened for the audit trail
 * while remaining decoupled from WordPress' hook names.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Cache;

/**
 * A change to an article that invalidates its cached representations.
 */
enum ContentChangeEvent: string {

	case Published = 'published';
	case Updated   = 'updated';
	case Deleted   = 'deleted';
}
