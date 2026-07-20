<?php
/**
 * Publishes a computed purge as a WordPress action.
 *
 * Which CDN to talk to is a deployment decision, not a delivery-platform one:
 * Fastly, Cloudflare, Varnish, and Akamai all take the same surrogate tags over
 * different APIs. So the platform stops at the point where the tag set is known
 * and correct, and hands it to WordPress. A site binds its own CDN client to
 * `hybrid_delivery_purge` and the wiring is complete; a site with no CDN binds
 * nothing and the purge is a harmless no-op rather than a hard dependency.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Cache;

/**
 * Broadcasts purge requests to whatever CDN adapter the site has bound.
 */
final class WpActionPurger implements CachePurger {

	/**
	 * The action a CDN adapter binds to. Receives the tag list and the event value.
	 */
	public const HOOK = 'hybrid_delivery_purge';

	/**
	 * Broadcast the purge.
	 *
	 * @param ContentChangeEvent $event The change that triggered the purge.
	 * @param string[]           $tags  Surrogate tags to invalidate.
	 *
	 * @return void
	 */
	public function purge( ContentChangeEvent $event, array $tags ): void {
		if ( ! function_exists( 'do_action' ) ) {
			return;
		}

		do_action( self::HOOK, $tags, $event->value );
	}
}
