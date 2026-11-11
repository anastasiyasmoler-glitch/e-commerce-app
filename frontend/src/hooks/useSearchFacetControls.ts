import { useCallback } from 'react'
import type { SearchParams } from '@/api/searchApi'
import { priceRangeFromFacetKey, toggleCategoryId } from '@/lib/search/searchParams'

export function useSearchFacetControls(
  searchParams: URLSearchParams,
  setSearchParams: (next: URLSearchParams) => void,
) {
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
    (sort: NonNullable<SearchParams['sort']>) => {
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
      if (page <= 1) {
        next.delete('page')
      } else {
        next.set('page', String(page))
      }
      setSearchParams(next)
    },
    [searchParams, setSearchParams],
  )

  return {
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
