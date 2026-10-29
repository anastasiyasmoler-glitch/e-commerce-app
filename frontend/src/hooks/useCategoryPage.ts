import { useCallback, useMemo } from 'react'
import { useParams, useSearchParams } from 'react-router-dom'
import { useSelectedStore } from '@/catalog/useSelectedStore'
import {
  CATALOG_DEFAULT_PER_PAGE,
  CATALOG_PER_PAGE_OPTIONS,
  parseCatalogListingParams,
  type CatalogPerPage,
} from '@/lib/catalog/catalogListingParams'
import { useCategoryListing } from '@/hooks/useProducts'

export type CategoryPageStatus = 'loading' | 'no-store' | 'error' | 'ready' | 'unknown-category'

export function useCategoryPage() {
  const { slug } = useParams()
  const [searchParams, setSearchParams] = useSearchParams()
  const { selectedStore, stores, selectStore, isPending: storesPending } = useSelectedStore()

  const { page, perPage } = useMemo(
    () => parseCatalogListingParams(searchParams),
    [searchParams],
  )

  const { roots, current, children, categoryBySlug, products, pageSize } = useCategoryListing(
    selectedStore?.id,
    slug,
    page,
    perPage,
  )

  const updateListingParams = useCallback(
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

  const setPage = useCallback(
    (value: number | ((prev: number) => number)) => {
      const nextPage = typeof value === 'function' ? value(page) : value
      const next = new URLSearchParams(searchParams)
      if (nextPage <= 1) {
        next.delete('page')
      } else {
        next.set('page', String(nextPage))
      }
      setSearchParams(next)
    },
    [page, searchParams, setSearchParams],
  )

  const setPerPage = useCallback(
    (nextPerPage: CatalogPerPage) => {
      updateListingParams((params) => {
        if (nextPerPage === CATALOG_DEFAULT_PER_PAGE) {
          params.delete('per_page')
        } else {
          params.set('per_page', String(nextPerPage))
        }
      })
    },
    [updateListingParams],
  )

  const storeName = selectedStore?.name ?? ''
  const catalogReady = selectedStore?.id !== undefined
  const loading =
    storesPending ||
    (catalogReady &&
      (roots.isLoading ||
        products.isLoading ||
        products.isFetching ||
        (slug ? categoryBySlug.isLoading : false)))

  const facetLinks = useMemo(
    () => [
      { slug: null as string | null, name: 'All' },
      ...(roots.data ?? []).map((category) => ({ slug: category.slug, name: category.name })),
    ],
    [roots.data],
  )

  const subcategoryLinks = useMemo(
    () => (children.data ?? []).map((category) => ({ slug: category.slug, name: category.name })),
    [children.data],
  )

  let status: CategoryPageStatus = 'ready'
  if (loading) {
    status = 'loading'
  } else if (!catalogReady) {
    status = 'no-store'
  } else if (slug && categoryBySlug.isSuccess && current === null) {
    status = 'unknown-category'
  } else if (products.isError) {
    status = 'error'
  }

  return {
    status,
    slug,
    page,
    setPage,
    perPage,
    setPerPage,
    perPageOptions: CATALOG_PER_PAGE_OPTIONS,
    storeName,
    selectedStore,
    stores,
    selectStore,
    storesPending,
    title: current?.name ?? 'Catalog',
    current,
    rows: products.data ?? [],
    pageSize,
    facetLinks,
    subcategoryLinks,
  }
}
