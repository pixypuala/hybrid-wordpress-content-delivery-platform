<?php
/**
 * The stable public shape of an article for headless consumers.
 *
 * This DTO is the *contract*. A React/Next front end (or any client) codes
 * against these fields, not against WordPress' internal post shape. Because the
 * contract is explicit and versioned, WordPress internals can change without
 * breaking consumers — the whole point of the hybrid architecture.
 *
 * @package Pixypuala\HybridDelivery
 */

declare( strict_types=1 );

namespace Pixypuala\HybridDelivery\Resource;

/**
 * Immutable article representation.
 */
final class ArticleResource {

	/**
	 * @param int      $id           Stable numeric id.
	 * @param string   $slug         URL slug.
	 * @param string   $title        Plain-text title.
	 * @param string   $excerpt      Plain-text excerpt.
	 * @param string   $html         Sanitised body HTML.
	 * @param string   $published_at ISO-8601 UTC timestamp.
	 * @param string   $author       Author display name.
	 * @param string[] $tags         Tag slugs.
	 */
	public function __construct(
		public readonly int $id,
		public readonly string $slug,
		public readonly string $title,
		public readonly string $excerpt,
		public readonly string $html,
		public readonly string $published_at,
		public readonly string $author,
		public readonly array $tags,
	) {}

	/**
	 * The wire representation. Field names are part of the contract and must
	 * only change with a contract version bump.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return array(
			'id'          => $this->id,
			'slug'        => $this->slug,
			'title'       => $this->title,
			'excerpt'     => $this->excerpt,
			'html'        => $this->html,
			'publishedAt' => $this->published_at,
			'author'      => $this->author,
			'tags'        => $this->tags,
		);
	}
}
