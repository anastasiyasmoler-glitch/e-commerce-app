import { useSelectedStore } from '@/catalog/useSelectedStore'
import { useProducts } from '@/hooks/useProducts'

export type HomePageStatus = 'loading' | 'no-store' | 'error' | 'ready'

export function useHomePage() {
  const { stores, selectedStore, selectStore, isPending: storesPending } = useSelectedStore()
  const storeId = selectedStore?.id

  const latestQuery = useProducts(storeId, { section: 'latest', per_page: 4, page: 1 })
  const moreQuery = useProducts(storeId, { section: 'more', per_page: 4, page: 2 })

  const storeName = selectedStore?.name ?? ''
  const catalogReady = storeId !== undefined
  const loading =
    storesPending || (catalogReady && (latestQuery.isLoading || moreQuery.isLoading))

  let status: HomePageStatus = 'ready'
  if (loading) {
    status = 'loading'
  } else if (!catalogReady) {
    status = 'no-store'
  } else if (latestQuery.isError || moreQuery.isError) {
    status = 'error'
  }

  return {
    status,
    stores,
    selectedStore,
    selectStore,
    storeName,
    latestProducts: latestQuery.data ?? [],
    moreProducts: moreQuery.data ?? [],
  }
}
