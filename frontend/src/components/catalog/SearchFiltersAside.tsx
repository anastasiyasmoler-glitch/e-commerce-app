import type { SearchFacetValue } from '@/api/searchApi'
import { Button } from '@/components/ui/button'
import {
  formatFacetAttributeName,
  formatPriceCents,
  formatPriceFacetLabel,
  priceRangeFromFacetKey,
} from '@/lib/search/searchParams'
import type { ReactNode } from 'react'

function isPriceFacetActive(
  facetKey: string,
  activeMin: number | undefined,
  activeMax: number | undefined,
): boolean {
  const range = priceRangeFromFacetKey(facetKey)
  return activeMin === range.price_min && activeMax === range.price_max
}

export type SearchFiltersAsideProps = {
  /** When false, only `beforeFacets` is shown (catalog browse / store). */
  showFacetFilters?: boolean
  beforeFacets?: ReactNode
  activeSort: NonNullable<'relevance' | 'price_asc' | 'price_desc'>
  setSort: (sort: 'relevance' | 'price_asc' | 'price_desc') => void
  activeAvailable: boolean | undefined
  setAvailableFilter: (value: boolean | undefined) => void
  availableFacets: SearchFacetValue[]
  priceFacets: SearchFacetValue[]
  activePriceMin: number | undefined
  activePriceMax: number | undefined
  applyPriceFacet: (facetKey: string) => void
  clearPriceFilter: () => void
  showCategoryFacets: boolean
  categories: SearchFacetValue[]
  activeCategoryIds: string[]
  toggleCategory: (categoryId: string) => void
  clearCategories: () => void
  attributeFacets: Record<string, SearchFacetValue[]>
  activeAttributes: Record<string, string>
  toggleAttribute: (code: string, value: string) => void
  clearAttributes: () => void
}

export function SearchFiltersAside({
  showFacetFilters = true,
  beforeFacets,
  activeSort,
  setSort,
  activeAvailable,
  setAvailableFilter,
  availableFacets,
  priceFacets,
  activePriceMin,
  activePriceMax,
  applyPriceFacet,
  clearPriceFilter,
  showCategoryFacets,
  categories,
  activeCategoryIds,
  toggleCategory,
  clearCategories,
  attributeFacets,
  activeAttributes,
  toggleAttribute,
  clearAttributes,
}: SearchFiltersAsideProps) {
  if (!showFacetFilters && !beforeFacets) {
    return null
  }

  return (
    <aside className="space-y-8 md:sticky md:top-[88px] md:shrink-0 md:self-start">
      {beforeFacets}

      {!showFacetFilters ? null : (
        <>
      <section>
        <h2 className="text-[13px] font-semibold tracking-[0.08em] text-muted uppercase">Sort</h2>
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
              onClick={() => setSort(value)}
              className={`rounded-[6px] border px-3 py-2 text-left text-[14px] transition-colors ${
                activeSort === value
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
        <h2 className="text-[13px] font-semibold tracking-[0.08em] text-muted uppercase">Availability</h2>
        <ul className="mt-3 space-y-2">
          <li>
            <button
              type="button"
              onClick={() => setAvailableFilter(undefined)}
              className={`flex w-full items-center justify-between rounded-[6px] border px-3 py-2 text-left text-[14px] transition-colors ${
                activeAvailable === undefined
                  ? 'border-brand bg-brand-soft text-ink'
                  : 'border-line text-ink hover:border-brand'
              }`}
            >
              <span>Any</span>
            </button>
          </li>
          {availableFacets.filter((facet) => facet.count > 0).map((facet) => {
            const inStock = facet.value === 'true'
            const active =
              (inStock && activeAvailable === true) || (!inStock && activeAvailable === false)
            return (
              <li key={facet.value}>
                <button
                  type="button"
                  onClick={() => setAvailableFilter(inStock ? true : false)}
                  className={`flex w-full items-center justify-between rounded-[6px] border px-3 py-2 text-left text-[14px] transition-colors ${
                    active
                      ? 'border-brand bg-brand-soft text-ink'
                      : 'border-line text-ink hover:border-brand'
                  }`}
                >
                  <span>{inStock ? 'In stock' : 'Out of stock'}</span>
                  <span className="text-muted">{facet.count}</span>
                </button>
              </li>
            )
          })}
        </ul>
      </section>

      <section>
        <div className="flex items-center justify-between gap-2">
          <h2 className="text-[13px] font-semibold tracking-[0.08em] text-muted uppercase">Price</h2>
          {activePriceMin !== undefined || activePriceMax !== undefined ? (
            <button
              type="button"
              className="text-[12px] text-brand hover:underline"
              onClick={clearPriceFilter}
            >
              Clear
            </button>
          ) : null}
        </div>
        {priceFacets.filter((facet) => facet.count > 0).length === 0 ? (
          <p className="mt-3 text-[14px] text-muted">No price facets yet.</p>
        ) : (
          <ul className="mt-3 space-y-2">
            {priceFacets
              .filter((facet) => facet.count > 0)
              .map((facet) => {
              const active = isPriceFacetActive(facet.value, activePriceMin, activePriceMax)
              return (
                <li key={facet.value}>
                  <button
                    type="button"
                    onClick={() => applyPriceFacet(facet.value)}
                    className={`flex w-full items-center justify-between rounded-[6px] border px-3 py-2 text-left text-[14px] transition-colors ${
                      active
                        ? 'border-brand bg-brand-soft text-ink'
                        : 'border-line text-ink hover:border-brand'
                    }`}
                  >
                    <span>{formatPriceFacetLabel(facet.value)}</span>
                    <span className="text-muted">{facet.count}</span>
                  </button>
                </li>
              )
            })}
          </ul>
        )}
        {activePriceMin !== undefined || activePriceMax !== undefined ? (
          <p className="mt-2 text-[13px] text-muted">
            {activePriceMin !== undefined ? formatPriceCents(activePriceMin) : 'Any'}
            {' – '}
            {activePriceMax !== undefined ? formatPriceCents(activePriceMax) : 'Any'}
          </p>
        ) : null}
      </section>

      {showCategoryFacets ? (
        <section>
          <h2 className="text-[13px] font-semibold tracking-[0.08em] text-muted uppercase">Category</h2>
          {categories.length === 0 ? (
            <p className="mt-3 text-[14px] text-muted">No category facets yet.</p>
          ) : (
              <ul className="mt-3 space-y-2">
                {categories.filter((facet) => facet.count > 0).map((facet) => {
                const active = activeCategoryIds.includes(facet.value)
                const label = facet.label ?? facet.value
                return (
                  <li key={facet.value}>
                    <button
                      type="button"
                      onClick={() => toggleCategory(facet.value)}
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
          {activeCategoryIds.length > 0 ? (
            <Button
              type="button"
              variant="outline"
              className="mt-4 w-full rounded-[6px]"
              onClick={clearCategories}
            >
              Clear categories
            </Button>
          ) : null}
        </section>
      ) : null}

      {Object.entries(attributeFacets)
        .filter(([, facets]) => facets.some((facet) => facet.count > 0))
        .map(([code, facets]) => (
        <section key={code}>
          <h2 className="text-[13px] font-semibold tracking-[0.08em] text-muted uppercase">
            {formatFacetAttributeName(code)}
          </h2>
          <ul className="mt-3 space-y-2">
            {facets.filter((facet) => facet.count > 0).map((facet) => {
              const active = activeAttributes[code] === facet.value
              return (
                <li key={`${code}-${facet.value}`}>
                  <button
                    type="button"
                    onClick={() => toggleAttribute(code, facet.value)}
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

      {Object.keys(activeAttributes).length > 0 ? (
        <Button
          type="button"
          variant="outline"
          className="w-full rounded-[6px]"
          onClick={clearAttributes}
        >
          Clear attributes
        </Button>
      ) : null}
        </>
      )}
    </aside>
  )
}
