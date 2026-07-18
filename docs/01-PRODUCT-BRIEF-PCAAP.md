# Product Brief — Hybrid WordPress Content Delivery Platform

## Outcome

Create a public, reproducible reference project that demonstrates the ability to solve a real content-heavy SaaS, global campaign platform, documentation publisher, media/marketing team problem from discovery through maintenance.

## Problem and cost

**Problem:** Teams often adopt headless WordPress for frontend freedom but lose editor preview, plugin behavior, redirects, search, draft security, cache clarity, and operational simplicity.

**Cost:** Publishing delays, stale pages, broken metadata, duplicated business rules, hard-to-debug cache bugs, and an unnecessarily complex platform.

## Users and jobs to be done

1. **Primary operator:** completes the central workflow without developer assistance.
2. **Administrator:** configures permissions, integrations, and policy safely.
3. **Developer/maintainer:** updates the system, diagnoses failures, and extends it through documented contracts.
4. **Reviewer/auditor:** verifies security, accessibility, performance, and release evidence.
5. **Recruiter/client:** understands the outcome and the developer's contribution without reading every source file.

## Functional scope

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

## Explicit non-goals for the first release

- no fake production scale or fabricated customers;
- no paid-vendor dependency required to run the core demo;
- no feature that exists only for a resume keyword;
- no hidden setup steps performed manually by the author;
- no broad compliance certification claim;
- no unsupported browser, PHP, WordPress, or provider promise.

## Acceptance outcomes

- The central workflow is documented as Given/When/Then scenarios.
- Every destructive action has authorization, confirmation, auditability where appropriate, and recovery documentation.
- Empty, loading, error, permission-denied, offline/unavailable, and stale-data states are designed.
- Accessibility is tested by keyboard and at least one screen-reader workflow, plus automation.
- Performance budgets are tied to user journeys, not a homepage-only score.
- CI produces useful artifacts when a test fails.
- A tagged release can be installed from a clean environment.
- The case study distinguishes measured results, fixture results, estimates, and unvalidated hypotheses.

## Success measures

Use measurements that the project can truthfully collect:

- task completion and error rate in a small documented usability test;
- regression count detected before release;
- build/test duration and flake rate;
- Core Web Vitals or controlled-lab journey metrics with environment stated;
- accessibility issues by severity and resolution status;
- query count/time for defined requests;
- recovery time during a scripted failure drill;
- external repository clones, issues, pull requests, or stars only as descriptive adoption data, never as quality proof.

## Ask

Inspect the architecture tradeoff record, trigger a publish-to-cache-invalidation flow, and review SEO/rendered-source parity.
