<?php
/**
 * Transforms a raw WordPress post array into a stable ArticleResource.
 *
 * This is the seam between WordPress internals and the public contract. It
 * validates required fields at the boundary, normalises timestamps to ISO-8601
 * UTC, and coerces types, so a malformed or partial post is rejected loudly
 * rather than leaking an inconsistent shape to consumers. Framework-free and
 * fully unit-tested; WordPress just supplies the raw array.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Transform;

use Pixypuala\HybridDelivery\Resource\ArticleResource;

/**
 * Builds article resources from raw source rows.
 */
final class ArticleTransformer {

	/**
	 * Transform one raw post.
	 *
	 * @param array<string, mixed> $raw Source row. Expected keys: id, slug,
	 *                                  title, excerpt, html, published_at
	 *                                  (any strtotime-parseable string or int),
	 *                                  author, tags (list of strings).
	 *
	 * @return ArticleResource
	 *
	 * @throws TransformException When a required field is missing or invalid.
	 */
	public function transform( array $raw ): ArticleResource {
		$id = $this->require_int( $raw, 'id' );
		if ( $id <= 0 ) {
			throw new TransformException( 'Article id must be a positive integer.' );
		}

		return new ArticleResource(
			id: $id,
			slug: $this->require_string( $raw, 'slug' ),
			title: $this->require_string( $raw, 'title' ),
			excerpt: $this->optional_string( $raw, 'excerpt' ),
			html: $this->optional_string( $raw, 'html' ),
			published_at: $this->to_iso8601( $raw['published_at'] ?? null ),
			author: $this->optional_string( $raw, 'author' ),
			tags: $this->string_list( $raw['tags'] ?? array() ),
		);
	}

	/**
	 * Transform many rows, preserving order.
	 *
	 * @param array<int, array<string, mixed>> $rows Source rows.
	 *
	 * @return ArticleResource[]
	 */
	public function transform_all( array $rows ): array {
		return array_map( array( $this, 'transform' ), array_values( $rows ) );
	}

	/**
	 * @param array<string, mixed> $raw Source row.
	 * @param string               $key Field name.
	 *
	 * @return int
	 *
	 * @throws TransformException When absent or non-numeric.
	 */
	private function require_int( array $raw, string $key ): int {
		if ( ! isset( $raw[ $key ] ) || ! is_numeric( $raw[ $key ] ) ) {
			throw new TransformException( sprintf( 'Missing or non-numeric field "%s".', $key ) );
		}
		return (int) $raw[ $key ];
	}

	/**
	 * @param array<string, mixed> $raw Source row.
	 * @param string               $key Field name.
	 *
	 * @return string
	 *
	 * @throws TransformException When absent or empty.
	 */
	private function require_string( array $raw, string $key ): string {
		$value = $raw[ $key ] ?? null;
		if ( ! is_string( $value ) || '' === trim( $value ) ) {
			throw new TransformException( sprintf( 'Missing or empty required field "%s".', $key ) );
		}
		return $value;
	}

	/**
	 * @param array<string, mixed> $raw Source row.
	 * @param string               $key Field name.
	 *
	 * @return string Empty string when absent.
	 */
	private function optional_string( array $raw, string $key ): string {
		$value = $raw[ $key ] ?? '';
		return is_string( $value ) ? $value : '';
	}

	/**
	 * Normalise a timestamp to ISO-8601 in UTC.
	 *
	 * @param mixed $value Unix seconds (int) or a parseable date string.
	 *
	 * @return string e.g. "2026-07-18T09:30:00+00:00".
	 *
	 * @throws TransformException When the value cannot be parsed.
	 */
	private function to_iso8601( mixed $value ): string {
		if ( null === $value || '' === $value ) {
			throw new TransformException( 'Missing required field "published_at".' );
		}

		if ( is_int( $value ) || ( is_string( $value ) && ctype_digit( $value ) ) ) {
			$timestamp = (int) $value;
		} else {
			$timestamp = strtotime( (string) $value );
			if ( false === $timestamp ) {
				throw new TransformException( sprintf( 'Unparseable published_at value: %s', (string) $value ) );
			}
		}

		return gmdate( 'c', $timestamp );
	}

	/**
	 * Coerce a value into a clean list of non-empty strings.
	 *
	 * @param mixed $value Candidate tag list.
	 *
	 * @return string[]
	 */
	private function string_list( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}
		$out = array();
		foreach ( $value as $item ) {
			if ( is_string( $item ) && '' !== trim( $item ) ) {
				$out[] = $item;
			}
		}
		return $out;
	}
}
