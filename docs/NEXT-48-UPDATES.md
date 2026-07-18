# Next 48 Updates — 03-HYBRID-WORDPRESS-CONTENT-DELIVERY-PLATFORM

## Why this file exists

This is a sequenced, honest backlog of at least 48 planned updates that keeps the repository genuinely active over time. Each item is a real unit of work (one issue or pull request) that advances capability, testing, security, documentation, or maintenance — not artificial busywork. Items are ordered so that early work unblocks later work, and grouped into six release milestones. Nothing here is claimed as already done: this is the forward plan.

## How to use it

Convert each checkbox into a tracked issue, attach it to the matching milestone, and close it with the pull request that satisfies it. Aim for a steady cadence (for example one to three items per week) so commit history, releases, and changelog entries reflect continuous, verifiable progress. Re-open or add items whenever revalidation, upstream releases, or user reports surface new work.

Total planned updates: **48** across **6** milestones.

## M1 — v0.1 Foundations & scaffolding

- [ ] **#01** Scaffold the Next.js delivery app and the WordPress content source of truth
- [ ] **#02** Define the content API contract (schema, pagination, error shapes)
- [ ] **#03** Set up a monorepo with shared types and a dev environment for both layers
- [ ] **#04** Add linting, type-checking, and formatting with pre-commit hooks
- [ ] **#05** Create ADRs: when headless is justified and the caching strategy
- [ ] **#06** Add CI that builds and type-checks both the app and the WordPress plugin
- [ ] **#07** Implement a typed API client generated from the content contract
- [ ] **#08** Establish structured logging across the API boundary

## M2 — v0.2 Core capability

- [ ] **#09** Implement incremental static regeneration for content pages
- [ ] **#10** Build authenticated draft preview with signed, expiring tokens
- [ ] **#11** Implement tag-based cache invalidation triggered by WordPress saves
- [ ] **#12** Add redirect and canonical-URL handling parity with WordPress
- [ ] **#13** Implement search backed by the WordPress index with a typed API
- [ ] **#14** Add SEO metadata, sitemaps, and structured data generation
- [ ] **#15** Build a fallback rendering path for API outages
- [ ] **#16** Add on-demand revalidation webhooks with signature verification

## M3 — v0.3 Testing, evidence & negative proof

- [ ] **#17** Add contract tests that fail when the API schema drifts
- [ ] **#18** Add a known-bad fixture: stale cache after publish must be caught
- [ ] **#19** Write E2E tests for preview, publish, and invalidation flows
- [ ] **#20** Add tests proving draft security (no unauthorized draft access)
- [ ] **#21** Add resilience tests injecting API latency and failure
- [ ] **#22** Create an evidence index tying each claim to a test
- [ ] **#23** Add coverage gates for the API client and rendering layer
- [ ] **#24** Add Lighthouse CI budgets for core templates

## M4 — v0.4 Security, compatibility & performance

- [ ] **#25** Threat-model the preview-token and webhook surfaces
- [ ] **#26** Add authorization tests ensuring WordPress remains the authz source
- [ ] **#27** Enforce a Content Security Policy on the delivery app
- [ ] **#28** Add a performance budget (LCP/CLS/INP) enforced in CI
- [ ] **#29** Define a Node/WordPress/PHP support matrix and test the floors
- [ ] **#30** Add cache-behavior observability and hit-rate metrics
- [ ] **#31** Document rollback for a bad frontend or contract release
- [ ] **#32** Add dependency and secret scanning to both layers

## M5 — v0.5 Documentation, DX & adoption

- [ ] **#33** Write a case study weighing headless vs coupled for this workload
- [ ] **#34** Record a demo and reproducible Playground/source blueprint
- [ ] **#35** Document the API contract and versioning policy for consumers
- [ ] **#36** Publish a caching-and-invalidation explainer for operators
- [ ] **#37** Add architecture diagrams for the request and invalidation paths
- [ ] **#38** Write a migration guide from fully-coupled to hybrid
- [ ] **#39** Document preview setup for editors
- [ ] **#40** Add a troubleshooting guide for cache and preview issues

## M6 — v1.0+ Community, release cadence & maintenance

- [ ] **#41** Adopt semantic versioning for the API contract and app
- [ ] **#42** Add protected-tag release automation with evidence attached
- [ ] **#43** Set a cadence to revalidate against new Next.js and WordPress releases
- [ ] **#44** Add a quarterly performance and SEO re-audit to the roadmap
- [ ] **#45** Publish a breaking-change and deprecation policy for the contract
- [ ] **#46** Triage issues with documented labels and SLAs
- [ ] **#47** Add 'good first issue' tasks for contract consumers
- [ ] **#48** Schedule recurring dependency-update and changelog review

## Honesty note

These updates are planned, not completed. They do not assert the software is already built, adopted, certified, bug-free, or secure in every environment. They describe the intended, testable path of work and the cadence for keeping the repository maintained.
