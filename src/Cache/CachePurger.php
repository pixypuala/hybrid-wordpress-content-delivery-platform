<?php
/**
 * Write port for purging cached representations by surrogate tag.
 *
 * The invalidation dispatcher computes *which* tags to purge; a CachePurger
 * decides *how*. A CDN adapter issues a tag-purge API call, an integration test
 * uses a fake that records calls. Keeping this an interface is what lets the
 * dispatcher be unit-tested without a live edge network.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Cache;

/**
 * Purges cached content identified by surrogate tags.
 */
interface CachePurger {

	/**
	 * Purge every representation carrying any of the given surrogate tags.
	 *
	 * @param ContentChangeEvent $event The change that triggered the purge (provenance).
	 * @param string[]           $tags  Surrogate tags to invalidate.
	 *
	 * @return void
	 */
	public function purge( ContentChangeEvent $event, array $tags ): void;
}
