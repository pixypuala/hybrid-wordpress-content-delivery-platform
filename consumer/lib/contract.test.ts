/**
 * Contract-parser unit tests.
 *
 * These verify the JavaScript twin of the PHP contract: a well-formed envelope
 * parses and narrows correctly, a version mismatch is rejected, and a malformed
 * row is rejected at the boundary. They run against the offline fixture, with no
 * network and no WordPress backend.
 */

import { describe, expect, it } from 'vitest';
import {
  ContractShapeError,
  ContractVersionError,
  SUPPORTED_CONTRACT_VERSION,
  parseArticleCollectionEnvelope,
  parseArticleEnvelope,
} from './contract';
import { fixtureArticleEnvelope, fixtureCollectionEnvelope } from './fixture';

describe('parseArticleCollectionEnvelope', () => {
  it('accepts a well-formed collection envelope and preserves every row', () => {
    const source = fixtureCollectionEnvelope();
    const parsed = parseArticleCollectionEnvelope(source);

    expect(parsed.meta.contractVersion).toBe(SUPPORTED_CONTRACT_VERSION);
    expect(parsed.data).toHaveLength(source.data.length);
    expect(parsed.data[0].slug).toBe(source.data[0].slug);
  });

  it('rejects an envelope whose contract version this consumer cannot read', () => {
    const incompatible = {
      ...fixtureCollectionEnvelope(),
      meta: { contractVersion: 2, generatedAt: '2026-07-18T00:00:00+00:00' },
    };

    expect(() => parseArticleCollectionEnvelope(incompatible)).toThrow(ContractVersionError);
  });

  it('rejects a row that violates the article shape', () => {
    const malformed = {
      meta: { contractVersion: SUPPORTED_CONTRACT_VERSION, generatedAt: '2026-07-18T00:00:00+00:00' },
      data: [{ id: 0, slug: '', title: '', excerpt: '', html: '', publishedAt: '', author: '', tags: [] }],
    };

    expect(() => parseArticleCollectionEnvelope(malformed)).toThrow(ContractShapeError);
  });
});

describe('parseArticleEnvelope', () => {
  it('accepts a well-formed single-article envelope', () => {
    const source = fixtureArticleEnvelope(1);
    expect(source).not.toBeNull();

    const parsed = parseArticleEnvelope(source);
    expect(parsed.data.id).toBe(1);
    expect(parsed.data.tags.length).toBeGreaterThan(0);
  });

  it('rejects a non-object payload', () => {
    expect(() => parseArticleEnvelope('not-json')).toThrow(ContractShapeError);
  });
});
