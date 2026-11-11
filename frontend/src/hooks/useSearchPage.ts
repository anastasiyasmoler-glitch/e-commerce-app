import { useMemo } from 'react'
import { useSearchParams } from 'react-router-dom'
import { useSelectedStore } from '@/catalog/useSelectedStore'
import { useStoreCategoryNames } from '@/hooks/useCategories'
import { useSearch } from '@/hooks/useSearch'
import { useSearchFacetControls } from '@/hooks/useSearchFacetControls'
import { parseSearchRequest } from '@/lib/search/searchParams'

export type SearchPageStatus = 'loading' | 'no-store' | 'error' | 'catalog-error' | 'ready'

export function useSearchPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const { selectedStore, isPending: storesPending, isError: storesError } = useSelectedStore()
  const storeId = selectedStore?.id
  const storeName = selectedStore?.name ?? ''
  const typedQuery = searchParams.get('q')?.trim() ?? ''

  const request = useMemo(
    () => (storeId !== undefined ? parseSearchRequest(searchParams, storeId) : null),
    [searchParams, storeId],
  )

  const searchQuery = useSearch(storeId, request ?? { store_id: 0, q: '' })
  const categoryNames = useStoreCategoryNames(storeId)

  const facetControls = useSearchFacetControls(searchParams, setSearchParams)

  const loading =
    storesPending ||
    (request !== null && storeId !== undefined && searchQuery.isLoading && !searchQuery.data)

  const isFetchingResults =
    request !== null && storeId !== undefined && searchQuery.isFetching

  let status: SearchPageStatus = 'ready'
  if (loading) {
    status = 'loading'
  } else if (storesError) {
    status = 'catalog-error'
  } else if (!request || !selectedStore) {
    status = 'no-store'
  } else if (searchQuery.isError) {
    status = 'error'
  }

  const result = searchQuery.data
  const items = result?.items ?? []
  const total = result?.pagination.total ?? 0
  const queryLabel = typedQuery || 'All products'

  return {
    status,
    isFetchingResults,
    storeName,
    request,
    result,
    items,
    total,
    queryLabel,
    categories: (result?.facets.category_id ?? []).map((facet) => ({
      ...facet,
      label: facet.label || categoryNames.data?.[facet.value] || facet.value,
    })),
    priceFacets: result?.facets.price ?? [],
    availableFacets: result?.facets.available ?? [],
    attributeFacets: result?.facets.attributes ?? {},
    activeCategoryIds: request?.category_id ?? [],
    activeAttributes: request?.attributes ?? {},
    activeSort: request?.sort ?? 'relevance',
    activeAvailable: request?.available,
    activePriceMin: request?.price_min,
    activePriceMax: request?.price_max,
    ...facetControls,
  }
}
