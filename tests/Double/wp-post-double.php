<?php
/**
 * Stand-in for WP_Post, limited to the three fields the subscriber reads.
 *
 * Defined only when WordPress is absent, so the same tests keep working if they
 * are ever run inside a real WordPress environment.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

if ( ! class_exists( 'WP_Post' ) ) {

	/**
	 * Minimal post double.
	 */
	final class WP_Post {

		/**
		 * @param int    $ID        Post id.
		 * @param string $post_type Post type.
		 */
		public function __construct(
			public readonly int $ID, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Mirrors WP_Post’s own public property name.
			public readonly string $post_type,
		) {}
	}
}
