# Hybrid WordPress Content Delivery Platform

## Portfolio purpose

A WordPress-backed content platform with a modern React/Next.js delivery layer that proves API design, cache invalidation, previews, SEO, resilience, and architectural tradeoffs.

This project is not considered complete when the UI looks good. It must demonstrate discovery, architecture, code quality, accessibility, security, performance, test design, deployment, recovery, documentation, and public communication.

## Getting started

Requires PHP 8.1+ and Composer.

```bash
composer install
composer test    # 32 unit tests: transformer + envelope + resolver + delivery handler + invalidation dispatcher
composer lint    # WordPress coding standards (PHPCS)
```

## What is built today

The **content contract** layer — the heart of the hybrid architecture — is implemented and tested:

- `ArticleResource` (`src/Resource/`) is the stable, versioned public shape a React/Next client
  codes against, decoupled from WordPress' internal post shape.
- `ArticleTransformer` (`src/Transform/`) is the seam: it validates required fields at the
  boundary, normalises timestamps to ISO-8601 UTC, and cleans tag lists, rejecting malformed
  source content loudly instead of leaking an inconsistent shape.
- `Envelope` (`src/Api/`) wraps every response with `contractVersion` + `generatedAt` so a CDN
  and client can reason about compatibility and freshness.
- A published JSON Schema (`schema/article.schema.json`) documents contract v1.
- `SurrogateKeyResolver` (`src/Cache/`) derives, deterministically, the cache key for an
  article at a given contract version plus the set of surrogate/invalidation tags to purge
  when it changes — the article's own tag, one per taxonomy term, and a global all-articles
  tag for listing responses. This is the invalidation *contract* the edge and the Next.js
  consumer share.
- `ArticleDeliveryHandler` (`src/Delivery/`) is the framework-free core of a delivery route:
  given an article id (or the whole collection) it fetches raw rows through a `ContentSource`
  port, runs them through the transformer, and returns a versioned `Envelope`. It is fully
  unit-tested without a web server. `RestRoute` (`src/Delivery/`) is the thin, guarded
  `register_rest_route` glue that adapts the handler's result and exceptions to WordPress REST
  responses; the DB-backed `ContentSource` that feeds it is wired by the plugin bootstrap.
- `InvalidationDispatcher` (`src/Cache/`) is the framework-free invalidation wiring: on a
  `ContentChangeEvent` (publish/update/delete) it derives the purge tag set via
  `SurrogateKeyResolver` and hands it to an injected `CachePurger` port. Unit-tested with a
  recording fake purger.
- `docs/architecture/ADR-headless-justified.md` records when headless WordPress is — and is
  not — justified, with per-surface criteria, tradeoffs, and alternatives considered.
- A **Next.js consumer** (`consumer/`) closes the JavaScript side of the boundary: a minimal
  App Router + TypeScript app that mirrors the versioned contract in TypeScript, has a typed
  `fetchArticles`/`fetchArticle` client that reads the envelope and enforces the contract
  version, and renders an article list + detail page. It builds and tests offline against a
  fixture shaped like the real envelope; pointing `DELIVERY_API_BASE_URL` at a running
  delivery backend switches it to live data. See `consumer/README.md`. Verified with a
  TypeScript type-check, a Vitest unit suite, and a Next production build.

The generally-useful part extracts to the `wp-content-contracts` open-source repo.

## Documented boundary (not yet built)

A live GraphQL server runtime and the live WordPress wiring remain environment-dependent:
the WordPress-backed `ContentSource`, the `add_action` hook registration that invokes the
invalidation dispatcher, and the concrete CDN `CachePurger` are wired in a live WordPress
environment. The framework-free delivery handler and invalidation dispatcher they depend on
are built and tested (above). The Next.js consumer (`consumer/`) is built and renders from a
fixture offline; showing *live* data additionally requires one of these delivery backends
running and reachable via `DELIVERY_API_BASE_URL`.

## PCAAP

### Problem

Teams often adopt headless WordPress for frontend freedom but lose editor preview, plugin behavior, redirects, search, draft security, cache clarity, and operational simplicity.

### Cost

Publishing delays, stale pages, broken metadata, duplicated business rules, hard-to-debug cache bugs, and an unnecessarily complex platform.

### Answer

Build a hybrid architecture: WordPress remains the editorial and canonical content system; a Next.js frontend renders selected experiences; conventional WordPress rendering remains available for fallback and comparison. Define typed contracts, preview authorization, webhook invalidation, observability, and graceful degradation.

### Advantage

The project proves that the developer can choose headless selectively rather than treating it as a fashion. The case study measures where hybrid delivery helps and where native WordPress is simpler.

### Proof required

- same content rendered natively and in Next.js
- typed schema/contract generation
- secure draft preview test
- publish/update/delete cache invalidation traces
- redirect and metadata parity crawl
- frontend failure fallback demonstration
- LCP/INP/CLS field or controlled lab evidence
- API rate/error/latency dashboard using synthetic data

### Ask

Inspect the architecture tradeoff record, trigger a publish-to-cache-invalidation flow, and review SEO/rendered-source parity.

## Intended audience

content-heavy SaaS, global campaign platform, documentation publisher, media/marketing team.

## Core stack and capabilities

- WordPress REST API as baseline; GraphQL only as an optional documented adapter
- PHP plugin defining canonical API fields and preview permissions
- Next.js with TypeScript and server rendering
- schema validation at API boundaries
- tag/path cache invalidation with signed webhooks
- image, redirect, sitemap and metadata pipeline
- Playwright, contract tests, accessibility tests and crawl checks
- OpenTelemetry-compatible traces or a lightweight documented equivalent
- local WordPress/Next.js environment and CI

## Product scope

- content model shared through explicit versioned contracts
- draft preview with short-lived authorization
- published content cache with deterministic invalidation
- redirect synchronization and 404 handling
- SEO metadata, canonical URLs, structured data and sitemaps
- search adapter with native fallback
- responsive media with alt-text and focal-point handling
- locale-aware routes and content fallbacks
- frontend outage mode and operational status page
- architecture comparison dashboard: native, hybrid and headless costs

## Major risks

- duplicating WordPress authorization logic in JavaScript
- exposing drafts or private media
- stale cache after deletes or redirects
- breaking plugin-rendered behavior silently
- claiming headless is automatically faster
- introducing GraphQL without a demonstrated need

## Milestone order

1. native reference implementation and content model
2. versioned API contract
3. Next.js public rendering
4. preview and invalidation
5. SEO/redirect/media parity
6. resilience and observability
7. performance/accessibility comparison
8. case study and reusable API-contract package

## Public repository opportunity

Extract the generally useful portion as `wp-content-contracts`. The public repository must have an open-source license, contribution guide, security policy, support boundary, reproducible local setup, release notes, and a roadmap that distinguishes committed work from ideas.

## Definition of portfolio-ready

- a stranger can run the project from a fresh clone;
- every major claim links to a test, report, trace, screenshot, or explicit limitation;
- no production credentials, personal data, copied proprietary code, or fake testimonials exist;
- repository issues reflect honest known gaps;
- the demo includes at least one controlled failure and recovery;
- architecture decisions explain alternatives and tradeoffs;
- the case study can be understood by both technical and nontechnical readers;
- the latest tagged release passes the documented support matrix.
