# Runtime verification

Everything below was executed against a live WordPress 7.0.2 install (PHP 8.2,
`portfolio.local`) with this repository symlinked in as an active plugin. The
unit suite proves the framework-free core; this document exists because the WP
adapters — `WpContentSource`, `RestRoute`, `PostChangeSubscriber`,
`WpActionPurger` — can only be proven by running them.

Reproduce with any WordPress install that has this plugin active and at least
one published post.

## Delivery API

```
$ curl -s http://portfolio.local/wp-json/hdp/v1/articles
{"meta":{"contractVersion":1,"generatedAt":"2026-07-20T19:50:10+00:00","total":1},
 "data":[{"id":1,"slug":"hello-world","title":"Hello world!", ... }]}

$ curl -s -o /dev/null -w '%{http_code}\n' http://portfolio.local/wp-json/hdp/v1/articles/5
200
$ curl -s -o /dev/null -w '%{http_code}\n' http://portfolio.local/wp-json/hdp/v1/articles/99999
404
$ curl -s -o /dev/null -w '%{http_code}\n' http://portfolio.local/wp-json/hdp/v1/articles/abc
404
```

A missing article returns 404 rather than an empty envelope, and a non-numeric
id never reaches the handler — the route's `validate_callback` rejects it.

## Contract conformance

The published shape was checked against an independently authored content
contract using `wp-content-contracts`, so conformance is asserted by a second
tool rather than by this repository's own tests:

```
$ wp content-contracts check-response article.contract.json \
    http://portfolio.local/wp-json/hdp/v1/articles/5 --json-path=data
Response satisfies the contract.          # exit 0

$ wp content-contracts check-response article.contract.json \
    http://portfolio.local/wp-json/hdp/v1/articles --json-path=data.0
Response satisfies the contract.          # exit 0
```

## Cache invalidation

A stand-in CDN adapter bound to `hybrid_delivery_purge` logged every purge while
real posts were edited through WP-CLI:

```
$ wp post update 5 --post_title='… (v2)'
PURGE[updated]: hdp:article:5, hdp:term:architecture, hdp:term:wordpress, hdp:articles

$ wp post update 5 --post_status=draft
PURGE[deleted]: hdp:article:5, hdp:articles

$ wp post update 5 --post_status=publish
PURGE[published]: hdp:article:5, hdp:term:architecture, hdp:term:wordpress, hdp:articles

$ wp post delete 7 --force
PURGE[deleted]: hdp:article:7, hdp:term:architecture, hdp:term:wordpress, hdp:articles
```

Note the unpublish case: the article is no longer readable, so the subscriber
falls back to the id-only tag set rather than skipping the purge. Serving a
deleted article from cache would be the worse failure.

## Error hygiene

`WP_DEBUG` and `WP_DEBUG_LOG` were enabled for every run above. `debug.log`
contains no notice, warning, deprecation, or fatal attributable to this plugin
across the front end, the REST routes, the block editor, and the site editor.

## What is still not proven here

Binding a real CDN client to `hybrid_delivery_purge` is deployment-specific and
was not executed: no Fastly/Cloudflare account is involved in this verification.
The tag set handed to that client is what the evidence above establishes.
