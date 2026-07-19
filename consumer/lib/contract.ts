/**
 * TypeScript mirror of the delivery API's versioned content contract.
 *
 * These types and guards are the JavaScript-side twin of the PHP `ArticleResource`
 * and `Envelope` (`src/Resource/`, `src/Api/`). The consumer codes against this
 * stable shape, never against WordPress internals. Runtime guards validate the
 * wire payload at the boundary and enforce the contract version, so an
 * incompatible or malformed response fails loudly rather than rendering garbage.
 */

/** Contract version this consumer understands. Mirrors `Envelope::CONTRACT_VERSION`. */
export const SUPPORTED_CONTRACT_VERSION = 1;

/** The stable public shape of an article (contract v1). */
export interface Article {
  readonly id: number;
  readonly slug: string;
  readonly title: string;
  readonly excerpt: string;
  readonly html: string;
  readonly publishedAt: string;
  readonly author: string;
  readonly tags: readonly string[];
}

/** Envelope metadata common to every response. Extra keys (e.g. `total`) are allowed. */
export interface EnvelopeMeta {
  readonly contractVersion: number;
  readonly generatedAt: string;
  readonly [key: string]: unknown;
}

/** The versioned response envelope. `data` is a single resource or a collection. */
export interface Envelope<TData> {
  readonly meta: EnvelopeMeta;
  readonly data: TData;
}

export type ArticleEnvelope = Envelope<Article>;
export type ArticleCollectionEnvelope = Envelope<readonly Article[]>;

/** Thrown when the server speaks a contract version this consumer cannot read. */
export class ContractVersionError extends Error {
  constructor(
    public readonly received: unknown,
    public readonly expected: number = SUPPORTED_CONTRACT_VERSION,
  ) {
    super(
      `Unsupported content contract version: received ${String(received)}, this consumer speaks v${expected}.`,
    );
    this.name = 'ContractVersionError';
  }
}

/** Thrown when a payload does not match the article contract shape. */
export class ContractShapeError extends Error {
  constructor(message: string) {
    super(`Malformed delivery payload: ${message}`);
    this.name = 'ContractShapeError';
  }
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null && !Array.isArray(value);
}

function assertArticle(value: unknown, where: string): Article {
  if (!isRecord(value)) {
    throw new ContractShapeError(`${where} is not an object.`);
  }
  const { id, slug, title, excerpt, html, publishedAt, author, tags } = value;
  if (typeof id !== 'number' || !Number.isInteger(id) || id < 1) {
    throw new ContractShapeError(`${where}.id must be a positive integer.`);
  }
  for (const [key, field] of Object.entries({ slug, title, excerpt, html, publishedAt, author })) {
    if (typeof field !== 'string') {
      throw new ContractShapeError(`${where}.${key} must be a string.`);
    }
  }
  if (!Array.isArray(tags) || tags.some((tag) => typeof tag !== 'string')) {
    throw new ContractShapeError(`${where}.tags must be an array of strings.`);
  }
  return {
    id,
    slug: slug as string,
    title: title as string,
    excerpt: excerpt as string,
    html: html as string,
    publishedAt: publishedAt as string,
    author: author as string,
    tags: tags as string[],
  };
}

function assertMeta(value: unknown): EnvelopeMeta {
  if (!isRecord(value)) {
    throw new ContractShapeError('envelope.meta is missing or not an object.');
  }
  if (value.contractVersion !== SUPPORTED_CONTRACT_VERSION) {
    throw new ContractVersionError(value.contractVersion);
  }
  if (typeof value.generatedAt !== 'string') {
    throw new ContractShapeError('envelope.meta.generatedAt must be a string.');
  }
  return value as EnvelopeMeta;
}

/** Validate and narrow a single-article envelope, enforcing the contract version. */
export function parseArticleEnvelope(payload: unknown): ArticleEnvelope {
  if (!isRecord(payload)) {
    throw new ContractShapeError('envelope is not an object.');
  }
  const meta = assertMeta(payload.meta);
  const data = assertArticle(payload.data, 'envelope.data');
  return { meta, data };
}

/** Validate and narrow a collection envelope, enforcing the contract version. */
export function parseArticleCollectionEnvelope(payload: unknown): ArticleCollectionEnvelope {
  if (!isRecord(payload)) {
    throw new ContractShapeError('envelope is not an object.');
  }
  const meta = assertMeta(payload.meta);
  if (!Array.isArray(payload.data)) {
    throw new ContractShapeError('envelope.data must be an array.');
  }
  const data = payload.data.map((row, index) => assertArticle(row, `envelope.data[${index}]`));
  return { meta, data };
}
