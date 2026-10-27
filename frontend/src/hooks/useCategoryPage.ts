import { useMemo, useState } from 'react'
import { useParams } from 'react-router-dom'
import { useSelectedStore } from '@/catalog/useSelectedStore'
import { useCategoryListing } from '@/hooks/useProducts'

export type CategoryPageStatus = 'loading' | 'no-store' | 'error' | 'ready'

export function useCategoryPage() {
  const { slug } = useParams()
  const { selectedStore } = useSelectedStore()
  const [page, setPage] = useState(1)
  const { roots, current, products, pageSize } = useCategoryListing(selectedStore?.id, slug, page)

  const storeName = selectedStore?.name ?? ''
  const catalogReady = selectedStore?.id !== undefined
  const loading = catalogReady && (roots.isLoading || products.isLoading || products.isFetching)

  const facetLinks = useMemo(
    () => [
      { slug: null as string | null, name: 'All' },
      ...(roots.data ?? []).map((category) => ({ slug: category.slug, name: category.name })),
    ],
    [roots.data],
  )

  let status: CategoryPageStatus = 'ready'
  if (loading) {
    status = 'loading'
  } else if (!catalogReady) {
    status = 'no-store'
  } else if (products.isError) {
    status = 'error'
  }

  return {
    status,
    slug,
    page,
    setPage,
    storeName,
    selectedStore,
    title: current?.name ?? 'Catalog',
    current,
    rows: products.data ?? [],
    pageSize,
    facetLinks,
  }
}
