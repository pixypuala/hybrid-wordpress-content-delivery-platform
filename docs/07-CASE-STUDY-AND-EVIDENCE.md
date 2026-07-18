# Public Case Study — Hybrid WordPress Content Delivery Platform

## Headline

Use an outcome that can be proven. Avoid “revolutionary,” “perfect,” “unbreakable,” or “enterprise-grade” without defined criteria.

## Required structure

### 1. Context

Describe the reference organization and clearly label it as a fixture, fictional scenario, internal project, client-approved case, or production system.

### 2. PCAAP

- **Problem:** Teams often adopt headless WordPress for frontend freedom but lose editor preview, plugin behavior, redirects, search, draft security, cache clarity, and operational simplicity.
- **Cost:** Publishing delays, stale pages, broken metadata, duplicated business rules, hard-to-debug cache bugs, and an unnecessarily complex platform.
- **Answer:** Build a hybrid architecture: WordPress remains the editorial and canonical content system; a Next.js frontend renders selected experiences; conventional WordPress rendering remains available for fallback and comparison. Define typed contracts, preview authorization, webhook invalidation, observability, and graceful degradation.
- **Advantage:** The project proves that the developer can choose headless selectively rather than treating it as a fashion. The case study measures where hybrid delivery helps and where native WordPress is simpler.
- **Proof:** link directly to reports and tagged code.
- **Ask:** Inspect the architecture tradeoff record, trigger a publish-to-cache-invalidation flow, and review SEO/rendered-source parity.

### 3. Your contribution

State what you personally designed, implemented, tested, documented, and reviewed. Credit collaborators and upstream projects.

### 4. Architecture decisions

Show one high-level diagram and three decisions with alternatives and tradeoffs.

### 5. Evidence

- same content rendered natively and in Next.js
- typed schema/contract generation
- secure draft preview test
- publish/update/delete cache invalidation traces
- redirect and metadata parity crawl
- frontend failure fallback demonstration
- LCP/INP/CLS field or controlled lab evidence
- API rate/error/latency dashboard using synthetic data

For each metric, include date, version/commit, environment, test data, tooling, sample size, and limitations.

### 6. Failures and changes

Describe at least one design or implementation decision that failed, what evidence exposed it, and how it changed. Honest correction demonstrates senior judgment.

### 7. What remains

List known gaps, deferred work, unsupported use cases, and the evidence needed before expanding claims.

## Evidence directory convention

```text
docs/evidence/
├── release-<version>/
│   ├── test-summary.md
│   ├── compatibility.json
│   ├── accessibility.md
│   ├── performance.md
│   ├── security-review.md
│   ├── screenshots/
│   └── traces/
└── README.md
```
