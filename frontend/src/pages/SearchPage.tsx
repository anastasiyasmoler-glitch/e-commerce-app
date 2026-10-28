import { Link } from 'react-router-dom'
import { SearchProductCard } from '@/components/catalog/SearchProductCard'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { PageLoading } from '@/components/ui/loading-screen'
import { useSearchPage } from '@/hooks/useSearchPage'
import { paths } from '@/paths'

export function SearchPage() {
  const search = useSearchPage()

  if (search.status === 'loading') {
    return <PageLoading label="Searching…" />
  }

  if (search.status === 'no-store') {
    return (
      <Container className="py-20 text-center">
        <h1 className="font-display text-[28px] font-medium">Select a store to search</h1>
        <Button asChild className="mt-6 rounded-[6px] bg-brand">
          <Link to={paths.home}>Back to home</Link>
        </Button>
      </Container>
    )
  }

  if (search.status === 'error') {
    return (
      <Container className="py-20 text-center">
        <h1 className="font-display text-[28px] font-medium">Search is unavailable</h1>
        <p className="mt-2 text-[15px] text-muted">Check that Search and OpenSearch are running.</p>
      </Container>
    )
  }

  const result = search.result!

  return (
    <Container className="py-10">
      <h1 className="font-display text-[32px] font-medium tracking-[-0.02em]">
        Search: {search.queryLabel}
      </h1>
      <p className="mt-2 text-[15px] text-muted">
        {search.total} result{search.total === 1 ? '' : 's'} in {search.storeName}
      </p>

      <div className="mt-10 grid gap-10 lg:grid-cols-[240px_1fr]">
        <aside>
          <h2 className="text-[13px] font-semibold tracking-[0.08em] text-muted uppercase">
            Categories
          </h2>
          {search.categories.length === 0 ? (
            <p className="mt-3 text-[14px] text-muted">No category facets yet.</p>
          ) : (
            <ul className="mt-3 space-y-2">
              {search.categories.map((facet) => {
                const active = search.activeCategoryIds.includes(facet.value)
                const label = facet.label ?? facet.value
                return (
                  <li key={facet.value}>
                    <button
                      type="button"
                      onClick={() => search.toggleCategory(facet.value)}
                      className={`flex w-full items-center justify-between rounded-[6px] border px-3 py-2 text-left text-[14px] transition-colors ${
                        active
                          ? 'border-brand bg-brand-soft text-ink'
                          : 'border-line text-ink hover:border-brand'
                      }`}
                    >
                      <span>{label}</span>
                      <span className="text-muted">{facet.count}</span>
                    </button>
                  </li>
                )
              })}
            </ul>
          )}
          {search.activeCategoryIds.length > 0 ? (
            <Button
              type="button"
              variant="outline"
              className="mt-4 w-full rounded-[6px]"
              onClick={search.clearCategories}
            >
              Clear categories
            </Button>
          ) : null}
        </aside>

        <section>
          {search.items.length === 0 ? (
            <p className="text-[15px] text-muted">No products match your search.</p>
          ) : (
            <div className="grid grid-cols-2 gap-x-3 gap-y-6 sm:gap-x-5 sm:gap-y-8 lg:grid-cols-3 xl:grid-cols-4">
              {search.items.map((product) => (
                <SearchProductCard key={product.id} product={product} storeName={search.storeName} />
              ))}
            </div>
          )}

          {result.pagination.total_pages > 1 ? (
            <div className="mt-10 flex items-center justify-center gap-3">
              <Button
                type="button"
                variant="outline"
                className="rounded-[6px]"
                disabled={result.pagination.page <= 1}
                onClick={() => search.goToPage(result.pagination.page - 1)}
              >
                Previous
              </Button>
              <span className="text-[14px] text-muted">
                Page {result.pagination.page} of {result.pagination.total_pages}
              </span>
              <Button
                type="button"
                variant="outline"
                className="rounded-[6px]"
                disabled={result.pagination.page >= result.pagination.total_pages}
                onClick={() => search.goToPage(result.pagination.page + 1)}
              >
                Next
              </Button>
            </div>
          ) : null}
        </section>
      </div>
    </Container>
  )
}
