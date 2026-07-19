/**
 * Typed delivery-API client.
 *
 * Reads the base URL from `DELIVERY_API_BASE_URL`. When it is set, the client
 * fetches the two real endpoints the PHP `RestRoute` registers — the article
 * collection and a single article by id — validates each response against the
 * versioned contract, and returns the narrowed `data`. When the variable is
 * unset, it serves the offline fixture so the app builds and renders without a
 * running backend. Contract-version and shape errors surface loudly.
 */

import {
  parseArticleCollectionEnvelope,
  parseArticleEnvelope,
} from './contract';
import type { Article } from './contract';
import { fixtureArticleEnvelope, fixtureCollectionEnvelope } from './fixture';

/** How long Next may cache a delivery response before revalidating, in seconds. */
const REVALIDATE_SECONDS = 60;

function baseUrl(): string | null {
  const value = process.env.DELIVERY_API_BASE_URL?.trim();
  return value ? value.replace(/\/+$/, '') : null;
}

async function getJson(url: string): Promise<unknown> {
  const response = await fetch(url, {
    headers: { accept: 'application/json' },
    next: { revalidate: REVALIDATE_SECONDS },
  });
  if (!response.ok) {
    throw new Error(`Delivery API responded ${response.status} for ${url}.`);
  }
  return response.json();
}

/** Fetch the full article collection, newest first. */
export async function fetchArticles(): Promise<Article[]> {
  const base = baseUrl();
  const envelope = base
    ? parseArticleCollectionEnvelope(await getJson(`${base}/articles`))
    : fixtureCollectionEnvelope();
  return [...envelope.data].sort(
    (left, right) => Date.parse(right.publishedAt) - Date.parse(left.publishedAt),
  );
}

/** Fetch a single article by id, or `null` when it does not exist. */
export async function fetchArticle(id: number): Promise<Article | null> {
  const base = baseUrl();
  if (!base) {
    return fixtureArticleEnvelope(id)?.data ?? null;
  }
  const response = await fetch(`${base}/articles/${id}`, {
    headers: { accept: 'application/json' },
    next: { revalidate: REVALIDATE_SECONDS },
  });
  if (response.status === 404) {
    return null;
  }
  if (!response.ok) {
    throw new Error(`Delivery API responded ${response.status} for article ${id}.`);
  }
  return parseArticleEnvelope(await response.json()).data;
}
