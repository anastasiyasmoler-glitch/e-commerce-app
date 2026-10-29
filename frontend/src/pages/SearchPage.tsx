import { Link } from 'react-router-dom'
import { SearchProductCard } from '@/components/catalog/SearchProductCard'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { PageLoading } from '@/components/ui/loading-screen'
import { useSearchPage } from '@/hooks/useSearchPage'
import { formatPriceCents, priceRangeFromFacetKey } from '@/lib/search/searchParams'
import { paths } from '@/paths'

function isPriceFacetActive(
  facetKey: string,
  activeMin: number | undefined,
  activeMax: number | undefined,
): boolean {
  const range = priceRangeFromFacetKey(facetKey)
  return activeMin === range.price_min && activeMax === range.price_max
}

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
        <aside className="space-y-8">
          <section>
            <h2 className="text-[13px] font-semibold tracking-[0.08em] text-muted uppercase">
              Sort
            </h2>
            <div className="mt-3 flex flex-col gap-2">
              {(
                [
                  ['relevance', 'Relevance'],
                  ['price_asc', 'Price: low to high'],
                  ['price_desc', 'Price: high to low'],
                ] as const
              ).map(([value, label]) => (
                <button
                  key={value}
                  type="button"
                  onClick={() => search.setSort(value)}
                  className={`rounded-[6px] border px-3 py-2 text-left text-[14px] transition-colors ${
                    search.activeSort === value
                      ? 'border-brand bg-brand-soft text-ink'
                      : 'border-line text-ink hover:border-brand'
                  }`}
                >
                  {label}
                </button>
              ))}
            </div>
          </section>

          <section>
            <h2 className="text-[13px] font-semibold tracking-[0.08em] text-muted uppercase">
              Availability
            </h2>
            <div className="mt-3 flex flex-col gap-2">
              <button
                type="button"
                onClick={() => search.setAvailableFilter(undefined)}
                className={`rounded-[6px] border px-3 py-2 text-left text-[14px] transition-colors ${
                  search.activeAvailable === undefined
                    ? 'border-brand bg-brand-soft text-ink'
                    : 'border-line text-ink hover:border-brand'
                }`}
              >
                Any
              </button>
              {search.availableFacets.map((facet) => {
                const inStock = facet.value === 'true'
                const active =
                  (inStock && search.activeAvailable === true) ||
                  (!inStock && search.activeAvailable === false)
                const label = inStock ? 'In stock' : 'Out of stock'
                return (
                  <button
                    key={facet.value}
                    type="button"
                    onClick={() => search.setAvailableFilter(inStock ? true : false)}
                    className={`flex items-center justify-between rounded-[6px] border px-3 py-2 text-left text-[14px] transition-colors ${
                      active
                        ? 'border-brand bg-brand-soft text-ink'
                        : 'border-line text-ink hover:border-brand'
                    }`}
                  >
                    <span>{label}</span>
                    <span className="text-muted">{facet.count}</span>
                  </button>
                )
              })}
            </div>
          </section>

          <section>
            <div className="flex items-center justify-between gap-2">
              <h2 className="text-[13px] font-semibold tracking-[0.08em] text-muted uppercase">
                Price
              </h2>
              {search.activePriceMin !== undefined || search.activePriceMax !== undefined ? (
                <button
                  type="button"
                  className="text-[12px] text-brand hover:underline"
                  onClick={search.clearPriceFilter}
                >
                  Clear
                </button>
              ) : null}
            </div>
            {search.priceFacets.length === 0 ? (
              <p className="mt-3 text-[14px] text-muted">No price ranges yet.</p>
            ) : (
              <ul className="mt-3 space-y-2">
                {search.priceFacets.map((facet) => {
                  const active = isPriceFacetActive(
                    facet.value,
                    search.activePriceMin,
                    search.activePriceMax,
                  )
                  return (
                    <li key={facet.value}>
                      <button
                        type="button"
                        onClick={() => search.applyPriceFacet(facet.value)}
                        className={`flex w-full items-center justify-between rounded-[6px] border px-3 py-2 text-left text-[14px] transition-colors ${
                          active
                            ? 'border-brand bg-brand-soft text-ink'
                            : 'border-line text-ink hover:border-brand'
                        }`}
                      >
                        <span>{facet.label ?? facet.value}</span>
                        <span className="text-muted">{facet.count}</span>
                      </button>
                    </li>
                  )
                })}
              </ul>
            )}
            {search.activePriceMin !== undefined || search.activePriceMax !== undefined ? (
              <p className="mt-2 text-[12px] text-muted">
                {search.activePriceMin !== undefined
                  ? formatPriceCents(search.activePriceMin)
                  : 'Any'}{' '}
                –{' '}
                {search.activePriceMax !== undefined
                  ? formatPriceCents(search.activePriceMax)
                  : 'Any'}
              </p>
            ) : null}
          </section>

          <section>
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
          </section>

          {Object.entries(search.attributeFacets).map(([code, facets]) => (
            <section key={code}>
              <h2 className="text-[13px] font-semibold tracking-[0.08em] text-muted uppercase">
                {code}
              </h2>
              <ul className="mt-3 space-y-2">
                {facets.map((facet) => {
                  const active = search.activeAttributes[code] === facet.value
                  return (
                    <li key={`${code}-${facet.value}`}>
                      <button
                        type="button"
                        onClick={() => search.toggleAttribute(code, facet.value)}
                        className={`flex w-full items-center justify-between rounded-[6px] border px-3 py-2 text-left text-[14px] transition-colors ${
                          active
                            ? 'border-brand bg-brand-soft text-ink'
                            : 'border-line text-ink hover:border-brand'
                        }`}
                      >
                        <span>{facet.label ?? facet.value}</span>
                        <span className="text-muted">{facet.count}</span>
                      </button>
                    </li>
                  )
                })}
              </ul>
            </section>
          ))}

          {Object.keys(search.activeAttributes).length > 0 ? (
            <Button
              type="button"
              variant="outline"
              className="w-full rounded-[6px]"
              onClick={search.clearAttributes}
            >
              Clear attributes
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
