import { useCallback, useMemo } from 'react'
import { useSearchParams } from 'react-router-dom'
import { useSelectedStore } from '@/catalog/useSelectedStore'
import { useSearch } from '@/hooks/useSearch'
import {
  parseSearchRequest,
  priceRangeFromFacetKey,
  toggleCategoryId,
} from '@/lib/search/searchParams'

export type SearchPageStatus = 'loading' | 'no-store' | 'error' | 'ready'

export function useSearchPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const { selectedStore } = useSelectedStore()
  const storeId = selectedStore?.id
  const storeName = selectedStore?.name ?? ''

  const request = useMemo(
    () => (storeId !== undefined ? parseSearchRequest(searchParams, storeId) : null),
    [searchParams, storeId],
  )

  const searchQuery = useSearch(storeId, request ?? { store_id: 0, q: '' })

  const updateParams = useCallback(
    (mutator: (params: URLSearchParams) => void, resetPage = true) => {
      const next = new URLSearchParams(searchParams)
      mutator(next)
      if (resetPage) {
        next.delete('page')
      }
      setSearchParams(next)
    },
    [searchParams, setSearchParams],
  )

  const toggleCategory = useCallback(
    (categoryId: string) => {
      updateParams((params) => {
        const current = params.getAll('category_id')
        const next = toggleCategoryId(current, categoryId)
        params.delete('category_id')
        for (const id of next) {
          params.append('category_id', id)
        }
      })
    },
    [updateParams],
  )

  const clearCategories = useCallback(() => {
    updateParams((params) => {
      params.delete('category_id')
    })
  }, [updateParams])

  const setSort = useCallback(
    (sort: 'relevance' | 'price_asc' | 'price_desc') => {
      updateParams((params) => {
        if (sort === 'relevance') {
          params.delete('sort')
        } else {
          params.set('sort', sort)
        }
      })
    },
    [updateParams],
  )

  const setAvailableFilter = useCallback(
    (value: boolean | undefined) => {
      updateParams((params) => {
        params.delete('available')
        if (value === true) {
          params.set('available', 'true')
        } else if (value === false) {
          params.set('available', 'false')
        }
      })
    },
    [updateParams],
  )

  const applyPriceRange = useCallback(
    (priceMin?: number, priceMax?: number) => {
      updateParams((params) => {
        params.delete('price_min')
        params.delete('price_max')
        if (priceMin !== undefined && priceMin >= 0) {
          params.set('price_min', String(priceMin))
        }
        if (priceMax !== undefined && priceMax >= 0) {
          params.set('price_max', String(priceMax))
        }
      })
    },
    [updateParams],
  )

  const applyPriceFacet = useCallback(
    (facetKey: string) => {
      const range = priceRangeFromFacetKey(facetKey)
      applyPriceRange(range.price_min, range.price_max)
    },
    [applyPriceRange],
  )

  const clearPriceFilter = useCallback(() => {
    updateParams((params) => {
      params.delete('price_min')
      params.delete('price_max')
    })
  }, [updateParams])

  const toggleAttribute = useCallback(
    (code: string, value: string) => {
      updateParams((params) => {
        const key = `attr.${code}`
        if (params.get(key) === value) {
          params.delete(key)
        } else {
          params.set(key, value)
        }
      })
    },
    [updateParams],
  )

  const clearAttributes = useCallback(() => {
    updateParams((params) => {
      for (const key of [...params.keys()]) {
        if (key.startsWith('attr.')) {
          params.delete(key)
        }
      }
    })
  }, [updateParams])

  const goToPage = useCallback(
    (page: number) => {
      const next = new URLSearchParams(searchParams)
      next.set('page', String(page))
      setSearchParams(next)
    },
    [searchParams, setSearchParams],
  )

  const loading =
    storeId === undefined || (request !== null && searchQuery.isLoading && !searchQuery.data)

  let status: SearchPageStatus = 'ready'
  if (loading) {
    status = 'loading'
  } else if (!request || !selectedStore) {
    status = 'no-store'
  } else if (searchQuery.isError) {
    status = 'error'
  }

  const result = searchQuery.data
  const items = result?.items ?? []
  const total = result?.pagination.total ?? 0
  const queryLabel = request?.q?.trim() ? request.q.trim() : 'All products'
  const categories = result?.facets.category_id ?? []
  const priceFacets = result?.facets.price ?? []
  const availableFacets = result?.facets.available ?? []
  const attributeFacets = result?.facets.attributes ?? {}
  const activeCategoryIds = request?.category_id ?? []
  const activeAttributes = request?.attributes ?? {}

  return {
    status,
    storeName,
    request,
    result,
    items,
    total,
    queryLabel,
    categories,
    priceFacets,
    availableFacets,
    attributeFacets,
    activeCategoryIds,
    activeAttributes,
    activeSort: request?.sort ?? 'relevance',
    activeAvailable: request?.available,
    activePriceMin: request?.price_min,
    activePriceMax: request?.price_max,
    toggleCategory,
    clearCategories,
    setSort,
    setAvailableFilter,
    applyPriceRange,
    applyPriceFacet,
    clearPriceFilter,
    toggleAttribute,
    clearAttributes,
    goToPage,
  }
}
