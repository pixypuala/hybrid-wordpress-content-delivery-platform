# Stack & commands

This repository has two runtimes:

- **PHP delivery library** (`src/`, `tests/`) — the framework-free content
  contract, delivery handler, and cache-invalidation core. Verified with
  Composer + PHPUnit + PHPCS.
- **Next.js consumer** (`consumer/`) — a minimal App Router application that
  renders articles from the delivery API's versioned envelope. Verified with
  a TypeScript type-check, a Vitest unit suite, and a production build.

The JS tooling uses `corepack pnpm` (Corepack pins the pnpm version from
`consumer/package.json`). Install once before running the gates below:

    corepack pnpm -C consumer install --ignore-workspace

## Machine-readable commands

The delivery gate reads the lines below. Each runs from the repository root and
scopes into `consumer/` in a subshell so the root stays package-manager-neutral.

type-check: (cd consumer && corepack pnpm exec tsc --noEmit)
test-unit: (cd consumer && corepack pnpm run test)
build: (cd consumer && corepack pnpm run build)

## PHP commands (run directly)

    composer install
    composer test
    composer lint
