=== Hybrid Delivery ===
Contributors: pixypuala
Tags: headless, rest-api, cache, cdn, content-api
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Serves a versioned, contract-stable article API for headless consumers, with surrogate-key cache invalidation on every content change.

== Description ==

Hybrid Delivery exposes your published content to a front end you control —
Next.js, Astro, a native app — through an explicit, versioned contract instead
of raw post internals. Consumers code against a stable `Article` shape, so
changes inside the CMS stop breaking clients downstream.

Every response is wrapped in an envelope carrying the contract version and the
instant it was generated. A consumer that receives a version it does not
support fails loudly rather than silently mis-rendering.

Content changes are translated into surrogate-key invalidations. Publishing,
editing, unpublishing, or deleting an article computes the exact tag set to
purge — the article, each of its terms, and the collection — and broadcasts it
on an action your CDN client binds to.

= Endpoints =

* `GET /wp-json/hdp/v1/articles` — the published collection.
* `GET /wp-json/hdp/v1/articles/<id>` — a single article, 404 when absent.

Both are read-only and public, exposing only already-published content.

= Cache invalidation =

Bind your CDN client to the `hybrid_delivery_purge` action:

`add_action( 'hybrid_delivery_purge', function ( array $tags, string $event ) {
	// $tags e.g. [ 'hdp:article:5', 'hdp:term:news', 'hdp:articles' ]
	my_cdn_purge_by_surrogate_key( $tags );
}, 10, 2 );`

With nothing bound, the purge is a harmless no-op — the plugin never assumes a
particular CDN.

== Frequently Asked Questions ==

= Does this replace the WordPress REST API? =

No. It adds one narrow, versioned read surface intended for headless
consumers. The core REST API is untouched.

= Does it expose drafts or private posts? =

No. Only published posts of the served post type are readable, and the routes
are read-only.

= What happens when the contract changes? =

Breaking changes bump `meta.contractVersion`. Consumers check that field and
refuse a version they were not written for.

== Screenshots ==

1. The versioned envelope and the article contract consumers code against.
2. The exact surrogate-key set computed for each content change.

== Changelog ==

= 0.1.0 =
* Initial release: versioned article delivery routes and surrogate-key cache
  invalidation on publish, update, unpublish, and delete.

== Upgrade Notice ==

= 0.1.0 =
Initial release.
