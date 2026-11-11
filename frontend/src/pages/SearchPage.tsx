import { Link } from 'react-router-dom'
import { SearchProductCard } from '@/components/catalog/SearchProductCard'
import { SearchFiltersAside } from '@/components/catalog/SearchFiltersAside'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { ContentLoader } from '@/components/ui/loading-screen'
import { useSearchPage } from '@/hooks/useSearchPage'
import { paths } from '@/paths'

export function SearchPage() {
  const search = useSearchPage()

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

  if (search.status === 'catalog-error') {
    return (
      <Container className="py-20 text-center">
        <h1 className="font-display text-[28px] font-medium">Catalog could not be loaded</h1>
        <p className="mt-2 text-[15px] text-muted">Please try again later.</p>
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

  const result = search.result
  const showLoader = search.status === 'loading' || search.isFetchingResults
  const showFacetFilters = !showLoader && search.total > 0

  return (
    <Container className="py-10">
      <h1 className="font-display text-[32px] font-medium tracking-[-0.02em]">
        Search: {search.queryLabel}
      </h1>
      <p className="mt-2 text-[15px] text-muted">
        {showLoader
          ? 'Searching…'
          : `${search.total} result${search.total === 1 ? '' : 's'} in ${search.storeName}`}
      </p>

      {showLoader ? (
        <ContentLoader label="Searching…" />
      ) : (
      <div className="mt-10 grid grid-cols-1 items-start gap-8 md:grid-cols-[240px_minmax(0,1fr)] md:gap-10">
        <SearchFiltersAside
          showFacetFilters={showFacetFilters}
          showCategoryFacets
          activeSort={search.activeSort}
          setSort={search.setSort}
          activeAvailable={search.activeAvailable}
          setAvailableFilter={search.setAvailableFilter}
          availableFacets={search.availableFacets}
          priceFacets={search.priceFacets}
          activePriceMin={search.activePriceMin}
          activePriceMax={search.activePriceMax}
          applyPriceFacet={search.applyPriceFacet}
          clearPriceFilter={search.clearPriceFilter}
          categories={search.categories}
          activeCategoryIds={search.activeCategoryIds}
          toggleCategory={search.toggleCategory}
          clearCategories={search.clearCategories}
          attributeFacets={search.attributeFacets}
          activeAttributes={search.activeAttributes}
          toggleAttribute={search.toggleAttribute}
          clearAttributes={search.clearAttributes}
        />

        <section className="min-w-0">
          {search.items.length === 0 ? (
            <p className="text-[15px] text-muted">No products match your search.</p>
          ) : (
            <div className="grid grid-cols-2 gap-x-3 gap-y-6 sm:gap-x-5 sm:gap-y-8 lg:grid-cols-3 xl:grid-cols-4">
              {search.items.map((product) => (
                <SearchProductCard key={product.id} product={product} storeName={search.storeName} />
              ))}
            </div>
          )}

          {result && result.pagination.total_pages > 1 ? (
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
      )}
    </Container>
  )
}
