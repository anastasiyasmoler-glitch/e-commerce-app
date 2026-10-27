import { useSelectedStore } from '@/catalog/useSelectedStore'
import { useCategories } from '@/hooks/useCategories'
import { useProducts } from '@/hooks/useProducts'

export type HomePageStatus = 'loading' | 'no-store' | 'error' | 'ready'

export function useHomePage() {
  const { stores, selectedStore, selectStore, isPending: storesPending } = useSelectedStore()
  const storeId = selectedStore?.id

  const categoriesQuery = useCategories(storeId)
  const latestQuery = useProducts(storeId, { section: 'latest', per_page: 4, page: 1 })
  const moreQuery = useProducts(storeId, { section: 'more', per_page: 4, page: 2 })

  const categories = (categoriesQuery.data ?? []).filter((category) => category.parent_id === null)
  const storeName = selectedStore?.name ?? ''
  const firstCategory = categories[0]
  const catalogReady = storeId !== undefined
  const loading =
    storesPending ||
    (catalogReady &&
      (categoriesQuery.isLoading || latestQuery.isLoading || moreQuery.isLoading))

  let status: HomePageStatus = 'ready'
  if (loading) {
    status = 'loading'
  } else if (!catalogReady) {
    status = 'no-store'
  } else if (categoriesQuery.isError || latestQuery.isError || moreQuery.isError) {
    status = 'error'
  }

  return {
    status,
    stores,
    selectedStore,
    selectStore,
    categories,
    storeName,
    firstCategory,
    latestProducts: latestQuery.data ?? [],
    moreProducts: moreQuery.data ?? [],
  }
}
