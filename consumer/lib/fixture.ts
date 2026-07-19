/**
 * A small offline dataset shaped exactly like the delivery API's envelopes.
 *
 * This lets the consumer build and render, and the unit tests run, without a
 * live WordPress backend. When `DELIVERY_API_BASE_URL` is unset the client
 * serves these envelopes; when it is set the client fetches the real API and
 * ignores this file entirely. The shape here is the same contract the PHP
 * `Envelope` emits, so it is a faithful stand-in, not a divergent mock.
 */

import { SUPPORTED_CONTRACT_VERSION } from './contract';
import type { Article, ArticleCollectionEnvelope, ArticleEnvelope } from './contract';

const GENERATED_AT = '2026-07-18T09:30:00+00:00';

const ARTICLES: readonly Article[] = [
  {
    id: 1,
    slug: 'why-hybrid-wordpress',
    title: 'Choosing Headless Selectively, Not by Fashion',
    excerpt:
      'WordPress stays the editorial system of record while a typed contract lets a modern frontend render selected surfaces.',
    html: '<p>The hybrid architecture keeps WordPress as the canonical editorial system and exposes a small, <strong>versioned</strong> contract to downstream consumers. Nothing on the frontend reaches into the internal post shape.</p><p>Because the contract is explicit, the CMS internals can change without breaking a single consumer.</p>',
    publishedAt: '2026-07-14T08:00:00+00:00',
    author: 'Editorial Team',
    tags: ['architecture', 'headless'],
  },
  {
    id: 2,
    slug: 'deterministic-cache-invalidation',
    title: 'Deterministic Cache Invalidation at the Edge',
    excerpt:
      'Every article carries a versioned cache key and a predictable set of surrogate tags, so a publish purges exactly what it should.',
    html: '<p>On publish, update, or delete, the platform derives the surrogate tags to purge from the same resolver the edge trusts. The consumer and the CDN share one invalidation contract.</p>',
    publishedAt: '2026-07-16T13:45:00+00:00',
    author: 'Platform Team',
    tags: ['caching', 'edge', 'architecture'],
  },
  {
    id: 3,
    slug: 'contracts-over-internals',
    title: 'Contracts Over Internals',
    excerpt:
      'A boundary validated at the seam rejects malformed content loudly instead of leaking an inconsistent shape to readers.',
    html: '<p>The transformer normalises timestamps to ISO-8601 UTC, cleans tag lists, and rejects partial rows at the boundary. Consumers receive a shape they can rely on, every time.</p>',
    publishedAt: '2026-07-17T17:20:00+00:00',
    author: 'Editorial Team',
    tags: ['contracts', 'quality'],
  },
];

/** The collection envelope the offline client returns for the article list. */
export function fixtureCollectionEnvelope(): ArticleCollectionEnvelope {
  return {
    meta: {
      contractVersion: SUPPORTED_CONTRACT_VERSION,
      generatedAt: GENERATED_AT,
      total: ARTICLES.length,
    },
    data: ARTICLES,
  };
}

/** The single-article envelope for a given id, or `null` when unknown. */
export function fixtureArticleEnvelope(id: number): ArticleEnvelope | null {
  const article = ARTICLES.find((candidate) => candidate.id === id);
  if (!article) {
    return null;
  }
  return {
    meta: {
      contractVersion: SUPPORTED_CONTRACT_VERSION,
      generatedAt: GENERATED_AT,
    },
    data: article,
  };
}
