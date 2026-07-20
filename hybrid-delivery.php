<?php
/**
 * Plugin Name:       Hybrid Delivery
 * Plugin URI:        https://github.com/pixypuala/hybrid-wordpress-content-delivery-platform
 * Description:        Serves a versioned, contract-stable article API for headless consumers, backed by WordPress content and surrogate-key cache invalidation.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Pixypuala
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hybrid-delivery
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery;

use Pixypuala\HybridDelivery\Cache\InvalidationDispatcher;
use Pixypuala\HybridDelivery\Cache\PostChangeSubscriber;
use Pixypuala\HybridDelivery\Cache\SurrogateKeyResolver;
use Pixypuala\HybridDelivery\Cache\WpActionPurger;
use Pixypuala\HybridDelivery\Delivery\ArticleDeliveryHandler;
use Pixypuala\HybridDelivery\Delivery\RestRoute;
use Pixypuala\HybridDelivery\Delivery\SystemClock;
use Pixypuala\HybridDelivery\Delivery\WpContentSource;
use Pixypuala\HybridDelivery\Transform\ArticleTransformer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prefer Composer's autoloader; fall back to a minimal PSR-4 loader.
$autoload = __DIR__ . '/vendor/autoload.php';
if ( is_readable( $autoload ) ) {
	require_once $autoload;
} else {
	spl_autoload_register(
		static function ( string $class_name ): void {
			$prefix = 'Pixypuala\\HybridDelivery\\';
			if ( ! str_starts_with( $class_name, $prefix ) ) {
				return;
			}
			$path = __DIR__ . '/src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';
			if ( is_readable( $path ) ) {
				require_once $path;
			}
		}
	);
}

// Register the read-only delivery routes.
add_action(
	'rest_api_init',
	static function (): void {
		$handler = new ArticleDeliveryHandler(
			new WpContentSource(),
			new ArticleTransformer(),
			new SystemClock()
		);

		( new RestRoute( $handler ) )->register();
	}
);

// Invalidate cached representations whenever an article changes. Bind a CDN
// client to WpActionPurger::HOOK to complete the purge for a given deployment.
add_action(
	'init',
	static function (): void {
		$dispatcher = new InvalidationDispatcher( new SurrogateKeyResolver(), new WpActionPurger() );

		( new PostChangeSubscriber( $dispatcher, new WpContentSource(), new ArticleTransformer() ) )->register();
	}
);
