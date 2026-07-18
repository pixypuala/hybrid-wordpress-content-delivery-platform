# Demo Script — Hybrid WordPress Content Delivery Platform

Keep the public demo between 6 and 10 minutes. Publish this script so the recording can be repeated after releases.

## 0:00–0:45 — Problem and scope

Explain the problem, who experiences it, and what the demo does not claim.

## 0:45–1:30 — Repository and reproducibility

Show the README, support matrix, one-command setup, license, security policy, and latest tagged release.

## 1:30–4:00 — Central user journey

Demonstrate the primary workflow using realistic fixture data. Use keyboard navigation for part of the journey and point out meaningful feedback/error states.

## 4:00–5:15 — Architecture

Show the high-level boundaries and one ADR. Explain why the chosen approach is preferable for this problem and what tradeoff it introduces.

## 5:15–6:30 — Controlled failure

Trigger a safe failure such as an invalid permission, provider timeout, incompatible version, broken assertion, stale cache, or interrupted job. Show diagnostics and recovery.

## 6:30–7:30 — Evidence

Open the CI run, test artifact, accessibility record, and performance report. State limitations.

## 7:30–8:15 — Open-source/community value

Show the extracted public package `wp-content-contracts`, contribution path, and one beginner-friendly issue.

## 8:15–9:00 — Ask

Inspect the architecture tradeoff record, trigger a publish-to-cache-invalidation flow, and review SEO/rendered-source parity.
