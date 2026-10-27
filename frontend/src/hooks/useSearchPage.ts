import { useCallback, useMemo } from 'react'
import { useSearchParams } from 'react-router-dom'
import { useSelectedStore } from '@/catalog/useSelectedStore'
import { useSearch } from '@/hooks/useSearch'
import { parseSearchRequest, toggleCategoryId } from '@/lib/search/searchParams'

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
  const activeCategoryIds = request?.category_id ?? []

  return {
    status,
    storeName,
    request,
    result,
    items,
    total,
    queryLabel,
    categories,
    activeCategoryIds,
    toggleCategory,
    clearCategories,
    goToPage,
  }
}
