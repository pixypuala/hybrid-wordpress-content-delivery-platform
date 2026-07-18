# Design Foundation — Hybrid WordPress Content Delivery Platform

- **Discipline (all aspects):** [`09-TEMPLATES/DESIGN-SYSTEM-CLAUDE.md`](../09-TEMPLATES/DESIGN-SYSTEM-CLAUDE.md) — surface pacing, scarce accent, 6-step type scale with negative tracking, editorial whitespace, hairline-over-shadow elevation.
- **Palette + fonts (override):** [`09-TEMPLATES/DESIGN-TOKENS-WORDPRESS.md`](../09-TEMPLATES/DESIGN-TOKENS-WORDPRESS.md) — wordpress.com landing palette (Blueberry `#3858E9` on white) + WordPress system font stack.

Expose the tokens as CSS variables / Tailwind theme in the Next.js layer so the headless frontend and the WordPress editor share one palette. Use the dark `code-window` surface for API/preview examples. Keep the white/dark pacing identical across SSR and client routes.

## Required accessibility extensions

The tokens are the visual language only. This project MUST meet WCAG 2.2 AA:

- Interactive targets ≥ **44×44px** (raise via padding).
- Every interactive element has a **visible, theme-aware focus ring** (2px offset, `--focus-ring`).
- Honor **prefers-reduced-motion** and **prefers-contrast**.
- Verify blue-on-white, blue-on-blue, and muted pairings hit AA; darken where they fail.
- All color via CSS variables / tokens — never inline hex.

## Rule

White canvas + one WordPress blue (`#3858E9`) + one dark product surface — no cream, no coral, no fourth tone. **Discipline** from Anthropic, **palette + type** from wordpress.com (Recoleta/Fraunces serif display + system-stack body + Space Mono, not Copernicus/StyreneB).
