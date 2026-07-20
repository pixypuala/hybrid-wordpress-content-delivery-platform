# Hybrid Delivery — Next.js consumer

A minimal, buildable Next.js (App Router, TypeScript) application that consumes
the hybrid WordPress **delivery API** through its versioned content contract.
It is the JavaScript side of the boundary the PHP library defines: the consumer
codes against the stable `Article` / `Envelope` shape, never against WordPress
internals.

## What it does

- **Mirrors the contract in TypeScript** (`lib/contract.ts`) — the twin of the
  PHP `ArticleResource` and `Envelope`. Runtime guards validate every payload at
  the boundary and enforce the contract version (`SUPPORTED_CONTRACT_VERSION`),
  so an incompatible or malformed response fails loudly.
- **Typed client** (`lib/client.ts`) — `fetchArticles()` reads the collection
  envelope and `fetchArticle(id)` reads a single-article envelope, matching the
  two routes the PHP `RestRoute` registers (`GET /articles`,
  `GET /articles/{id}`). Both read the `meta.contractVersion` field and return
  only the narrowed `data`.
- **Renders a list and a detail page** — `/` lists articles from the collection;
  `/articles/[id]` renders one article. Routing is by id to mirror the delivery
  API's single-article lookup exactly.

## Live backend vs. offline fixture

The client reads the API base URL from `DELIVERY_API_BASE_URL`:

- **Unset (default):** the client serves the offline dataset in `lib/fixture.ts`,
  shaped exactly like the real envelopes. This is what lets the app build,
  prerender, and pass tests with no running WordPress backend.
- **Set:** the client fetches the real delivery API and validates each response
  against the contract.

Point it at a running delivery backend:

    # consumer/.env.local
    DELIVERY_API_BASE_URL=https://your-wordpress-site.example/wp-json/hdp/v1

The base URL is the REST namespace root; the client appends `/articles` and
`/articles/{id}`.

This has been run end to end against a live WordPress 7.0.2 install with the
repository's plugin (`hybrid-delivery.php`) active. Pointed at
`http://portfolio.local/wp-json/hdp/v1`, `next build` collected the real post
ids from the live collection endpoint and prerendered `/articles/1`,
`/articles/5`, and `/articles/6`; the running server rendered those posts'
titles, authors, dates, and tags, and returned 404 for an id the API does not
have. No fixture is involved on that path — the contract guards validated real
WordPress output. See `docs/RUNTIME-VERIFICATION.md` at the repository root.

## Content trust boundary

Article body HTML is rendered via `dangerouslySetInnerHTML`. This is
intentional and safe within the architecture: the body is sanitised
server-side and guaranteed by the content contract (the PHP `ArticleResource`
documents `html` as sanitised body HTML). Sanitisation is the CMS/transformer's
responsibility at the source; duplicating it in the renderer would split one
trust boundary into two.

## Commands

Corepack pins the pnpm version from `package.json`.

    corepack pnpm install --ignore-workspace   # install dependencies
    corepack pnpm run dev                       # local dev server
    corepack pnpm exec tsc --noEmit             # type-check
    corepack pnpm run test                      # Vitest unit suite
    corepack pnpm run build                     # production build

The lockfile and `.next/` build output are intentionally not committed; the
delivery gate resolves dependencies fresh.
