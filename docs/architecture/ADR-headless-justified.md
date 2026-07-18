# ADR: When Headless WordPress Is (and Is Not) Justified

- **Status:** Accepted
- **Date:** 2026-07-18
- **Deciders:** Platform architecture
- **Related:** `docs/02-ARCHITECTURE-AND-ADRS.md` (ADR 5 rendering approach, ADR 8 caching and invalidation)

## Context

"Headless WordPress" means keeping WordPress as the editorial and canonical
content system while a separate application — here a Next.js frontend — renders
the public experience over a versioned API. It is fashionable, and that is
precisely the risk: teams adopt it as a default rather than as a decision, then
pay for capabilities WordPress gave them for free.

This platform is deliberately **hybrid**, not headless-everywhere. Traditional
WordPress rendering remains available as a reference and a fallback. That framing
forces the real question this record answers: for a given surface, is decoupling
the frontend worth what decoupling costs?

The decision matters because the costs are not symmetric with the benefits. The
benefits (rendering freedom, independent deploys, a typed contract) accrue to the
engineering team. The costs (lost preview fidelity, duplicated authorization,
cache-invalidation complexity, SEO parity work, an extra runtime to operate)
land on editors, on security, and on operations — often after launch, when they
are expensive to reverse.

### What WordPress gives you natively, that a headless layer must re-earn

- **Editor preview** of unpublished drafts, rendered exactly as they will appear.
- **Plugin-rendered behavior** (forms, shortcodes, related-content, embeds) that
  emits markup at render time.
- **Redirect management, search, sitemaps, and canonical URLs** wired into the
  request lifecycle.
- **Draft and private-content security** enforced by one authorization model.
- **A single cache story**: object cache plus page cache, purged by core.

A headless surface must reproduce each of these, correctly, in a second codebase.
Every item on that list is a place where a naive port silently loses a capability.

## Decision

Adopt headless delivery **per surface, against explicit criteria** — never as a
blanket architecture. A surface qualifies for headless delivery only when a
majority of the following hold, and none of the disqualifiers apply.

### Headless is justified when

1. **The read path dominates and is cache-friendly.** High-traffic, mostly-static
   content (articles, docs, landing pages) where a CDN edge serves most requests
   and invalidation is tag-driven and deterministic.
2. **The frontend needs interactivity WordPress themes serve poorly.** App-like
   views, client state, componentized design systems shared with non-WordPress
   products, or a React ecosystem the team already owns.
3. **The content model is stable and expressible as a typed contract.** The fields
   consumers need are well understood and change rarely, so a versioned contract
   (see `src/Resource/ArticleResource.php`) is cheap to maintain.
4. **Independent deployment cadence has real value.** Frontend and editorial
   release on different rhythms, and coupling them slows both.
5. **The team can operate a second runtime.** There is capacity to own a Node
   application: its deploys, observability, incident response, and on-call.

### Headless is NOT justified when

1. **Editors depend on high-fidelity preview and rich plugin rendering.** If the
   editorial workflow leans on live preview of drafts and plugin-generated markup,
   a decoupled frontend degrades the daily experience of the people who matter
   most. Native rendering is the stronger fit here.
2. **The content model churns.** A schema that changes weekly turns the contract
   into a maintenance tax paid on both sides of the wire.
3. **SEO and redirect parity are business-critical and under-resourced.** Metadata,
   structured data, canonical URLs, and legacy redirects must match the native
   baseline exactly; reproducing them is real, ongoing work, not a launch task.
4. **Traffic is low or write-heavy.** If the CDN cache-hit ratio is poor, the edge
   buys little and the added architecture is pure overhead.
5. **The team cannot absorb the operational surface.** A second runtime with no one
   to run it is an outage waiting to happen.

### GraphQL specifically

GraphQL is treated as an **optional documented adapter, gated behind demonstrated
need** — not a baseline. The WordPress REST API plus the versioned envelope
(`src/Api/Envelope.php`) is the default transport. Introduce GraphQL only when a
concrete consumer requirement (deep nested selection, client-driven field
shaping, aggregation across resources) is shown to be poorly served by REST.
Adding a query language without that evidence imports schema, resolver, and
depth-limiting complexity for no return.

## Consequences

### Positive

- Each headless surface is a defensible choice with named tradeoffs, so the case
  study can measure where hybrid delivery helped and where native was simpler.
- The versioned contract decouples consumers from WordPress internals, so the CMS
  can evolve without breaking the frontend.
- Deterministic, tag-based invalidation (`src/Cache/SurrogateKeyResolver.php`,
  driven by `src/Cache/InvalidationDispatcher.php`) keeps the edge honest on
  publish, update, and delete.

### Negative / accepted costs

- **Duplicated authorization risk.** Any permission logic reproduced in the
  frontend must stay server-authoritative; drafts and private media must never be
  served by a permissive read route. This is the single largest failure mode.
- **Two runtimes to operate and observe.** More surface for incidents, more places
  for a partial failure, so a frontend outage mode and native fallback are
  mandatory, not optional.
- **Parity work is continuous.** SEO metadata, redirects, and preview fidelity
  need ongoing verification against the native reference, not a one-time port.

### Reversibility

Because the platform stays hybrid, a surface that proves a poor headless fit can
fall back to native WordPress rendering without a rewrite. That escape hatch is a
core reason to keep the traditional render path alive rather than deleting it.

## Alternatives considered

1. **Fully headless (headless-everywhere).** Rejected. It maximizes rendering
   freedom but forces every surface — including low-traffic, preview-heavy,
   plugin-dependent ones — to re-earn native capabilities. The cost lands on
   editors and operations regardless of whether a given surface benefits.
2. **Fully traditional (no decoupling).** Rejected as the sole strategy. It keeps
   preview, plugins, redirects, and a single cache story for free, but caps the
   interactivity and design-system reuse the qualifying surfaces genuinely need.
3. **Hybrid, per-surface (chosen).** WordPress stays canonical; selected surfaces
   go headless against the criteria above; native rendering remains as reference
   and fallback. It carries the cost of maintaining two render paths, and that
   cost is accepted as the price of choosing headless selectively and reversibly.
4. **Static site generation from an export.** Rejected for this content profile.
   Full rebuilds do not fit frequently-updated editorial content or authorized
   draft preview; tag-based edge invalidation on live content is the better match.

## Verification

The claims in this record are backed, not asserted:

- The typed contract and its boundary validation are unit-tested
  (`tests/ArticleTransformerTest.php`, `tests/EnvelopeTest.php`).
- The delivery-route logic is unit-tested without a web server
  (`tests/ArticleDeliveryHandlerTest.php`).
- Deterministic invalidation on publish, update, and delete is unit-tested with a
  recording purger (`tests/SurrogateKeyResolverTest.php`,
  `tests/InvalidationDispatcherTest.php`).

The Next.js consumer application and a live GraphQL runtime are environment-
dependent and are exercised outside this record, in the demo environment.
