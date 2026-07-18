<?php
/**
 * Recording purger for invalidation-dispatcher tests.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Tests\Double;

use Pixypuala\HybridDelivery\Cache\CachePurger;
use Pixypuala\HybridDelivery\Cache\ContentChangeEvent;

/**
 * Captures every purge call so tests can assert event provenance and tag sets.
 */
final class FakeCachePurger implements CachePurger {

	/** @var array<int, array{event: ContentChangeEvent, tags: string[]}> */
	public array $calls = array();

	public function purge( ContentChangeEvent $event, array $tags ): void {
		$this->calls[] = array(
			'event' => $event,
			'tags'  => $tags,
		);
	}
}
