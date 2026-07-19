import Link from 'next/link';
import { fetchArticles } from '@/lib/client';
import { formatDate } from '@/lib/format';

export default async function HomePage() {
  const articles = await fetchArticles();

  return (
    <main className="shell">
      <header className="masthead">
        <p className="masthead__eyebrow">Hybrid delivery consumer</p>
        <h1 className="masthead__title">The Content Contract, Rendered</h1>
        <p className="masthead__lede">
          Every article below is read from the delivery API&rsquo;s versioned envelope and
          validated against the same contract the PHP layer emits. WordPress internals stay
          hidden; only the stable shape crosses the boundary.
        </p>
      </header>

      <ul className="article-list">
        {articles.map((article) => (
          <li key={article.id}>
            <Link href={`/articles/${article.id}`} className="card">
              <div className="card__meta">
                <span>{formatDate(article.publishedAt)}</span>
                <span aria-hidden="true">&middot;</span>
                <span>{article.author}</span>
              </div>
              <h2 className="card__title">{article.title}</h2>
              <p className="card__excerpt">{article.excerpt}</p>
              <ul className="tags">
                {article.tags.map((tag) => (
                  <li key={tag} className="tag">
                    {tag}
                  </li>
                ))}
              </ul>
            </Link>
          </li>
        ))}
      </ul>
    </main>
  );
}
