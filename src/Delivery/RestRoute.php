<?php
/**
 * Thin, guarded WordPress glue around the framework-free delivery handler.
 *
 * Everything that reasons about content lives in ArticleDeliveryHandler, which is
 * unit-tested without WordPress. This class is the deliberately thin transport
 * shim: it registers two read routes and translates the handler's return value
 * and exceptions into WordPress REST responses. The register() call is guarded so
 * the file loads and lints outside a WordPress runtime; the DB-backed
 * ContentSource that feeds the handler is wired by the plugin bootstrap and is
 * the only piece that genuinely requires a live WordPress environment.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Delivery;

use Pixypuala\HybridDelivery\Transform\TransformException;

/**
 * Registers article delivery routes and adapts handler results to REST responses.
 */
final class RestRoute {

	/**
	 * @param ArticleDeliveryHandler $handler        Builds the versioned envelopes.
	 * @param string                 $rest_namespace REST namespace, e.g. "hdp/v1".
	 */
	public function __construct(
		private readonly ArticleDeliveryHandler $handler,
		private readonly string $rest_namespace = 'hdp/v1',
	) {}

	/**
	 * Register the collection and single-article routes.
	 *
	 * Guarded so the class is safe to autoload and lint outside WordPress.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! function_exists( 'register_rest_route' ) ) {
			return;
		}

		register_rest_route(
			$this->rest_namespace,
			'/articles',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_collection' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$this->rest_namespace,
			'/articles/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_single' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'validate_callback' => static function ( $value ): bool {
							return is_numeric( $value ) && (int) $value > 0;
						},
					),
				),
			)
		);
	}

	/**
	 * Serve one article, mapping domain failures to REST error responses.
	 *
	 * @param \WP_REST_Request $request Inbound request carrying the id path param.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_single( \WP_REST_Request $request ) {
		$id = (int) $request['id'];

		try {
			$envelope = $this->handler->article( $id );
		} catch ( NotFoundException $error ) {
			return new \WP_Error( 'hdp_not_found', $error->getMessage(), array( 'status' => 404 ) );
		} catch ( TransformException $error ) {
			return new \WP_Error( 'hdp_invalid_content', $error->getMessage(), array( 'status' => 500 ) );
		}

		return rest_ensure_response( $envelope->to_array() );
	}

	/**
	 * Serve the article collection.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_collection() {
		return rest_ensure_response( $this->handler->collection()->to_array() );
	}
}
