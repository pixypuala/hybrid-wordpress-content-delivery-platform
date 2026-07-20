<?php
/**
 * Framework-free delivery handler: raw source rows in, versioned envelope out.
 *
 * This is the core of a delivery route with WordPress removed. Given an article
 * id (or the whole collection) it fetches raw rows from a ContentSource, runs
 * them through the ArticleTransformer to enforce the public contract, and wraps
 * the result in a versioned Envelope stamped with the current instant. The
 * actual register_rest_route call is thin glue around this object, so the
 * request-to-response logic is fully unit-testable without a web server.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Delivery;

use Pixypuala\HybridDelivery\Api\Envelope;
use Pixypuala\HybridDelivery\Resource\ArticleResource;
use Pixypuala\HybridDelivery\Transform\ArticleTransformer;

/**
 * Builds versioned response envelopes for article delivery routes.
 */
final class ArticleDeliveryHandler {

	/**
	 * @param ContentSource      $source      Supplies raw article rows.
	 * @param ArticleTransformer $transformer Enforces the public contract.
	 * @param Clock              $clock       Stamps the envelope's generatedAt.
	 */
	public function __construct(
		private readonly ContentSource $source,
		private readonly ArticleTransformer $transformer,
		private readonly Clock $clock,
	) {}

	/**
	 * Envelope for a single article.
	 *
	 * @param int $id Positive article id.
	 *
	 * @return Envelope
	 *
	 * @throws NotFoundException When no article matches the id.
	 * @throws \Pixypuala\HybridDelivery\Transform\TransformException When the row is malformed.
	 */
	public function article( int $id ): Envelope {
		$raw = $this->source->find( $id );
		if ( null === $raw ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Framework-free domain: this message is caught at the WordPress boundary and never reaches a response.
			throw new NotFoundException( sprintf( 'No article with id %d.', $id ) );
		}

		$resource = $this->transformer->transform( $raw );

		return new Envelope( $resource->to_array(), $this->clock->now_iso8601() );
	}

	/**
	 * Envelope for the full article collection, with a total count in meta.
	 *
	 * @return Envelope
	 *
	 * @throws \Pixypuala\HybridDelivery\Transform\TransformException When any row is malformed.
	 */
	public function collection(): Envelope {
		$resources = $this->transformer->transform_all( $this->source->all() );

		$data = array_map(
			static fn ( ArticleResource $article ): array => $article->to_array(),
			$resources
		);

		return new Envelope(
			$data,
			$this->clock->now_iso8601(),
			array( 'total' => count( $data ) )
		);
	}
}
