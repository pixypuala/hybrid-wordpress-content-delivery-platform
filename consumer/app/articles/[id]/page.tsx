import Link from 'next/link';
import { notFound } from 'next/navigation';
import type { Metadata } from 'next';
import { fetchArticle, fetchArticles } from '@/lib/client';
import { formatDate } from '@/lib/format';

type PageParams = { id: string };

/** Prerender one page per known article at build time. */
export async function generateStaticParams(): Promise<PageParams[]> {
  const articles = await fetchArticles();
  return articles.map((article) => ({ id: String(article.id) }));
}

export async function generateMetadata({
  params,
}: {
  params: Promise<PageParams>;
}): Promise<Metadata> {
  const { id } = await params;
  const article = await fetchArticle(Number(id));
  if (!article) {
    return { title: 'Article not found' };
  }
  return { title: article.title, description: article.excerpt };
}

export default async function ArticlePage({
  params,
}: {
  params: Promise<PageParams>;
}) {
  const { id } = await params;
  const article = await fetchArticle(Number(id));

  if (!article) {
    notFound();
  }

  return (
    <main className="shell">
      <Link href="/" className="backlink">
        &larr; All articles
      </Link>

      <article className="reader">
        <ul className="tags">
          {article.tags.map((tag) => (
            <li key={tag} className="tag">
              {tag}
            </li>
          ))}
        </ul>

        <h1 className="reader__title">{article.title}</h1>

        <div className="reader__meta">
          <span>{formatDate(article.publishedAt)}</span>
          <span aria-hidden="true">&middot;</span>
          <span>{article.author}</span>
        </div>

        {/* Body HTML is sanitised server-side and guaranteed by the content contract. */}
        <div
          className="reader__body"
          dangerouslySetInnerHTML={{ __html: article.html }}
        />

        <p className="provenance">
          Rendered from delivery contract v1 &middot; article #{article.id} &middot; slug{' '}
          {article.slug}
        </p>
      </article>
    </main>
  );
}
