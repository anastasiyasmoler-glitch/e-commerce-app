import { useState, type ReactNode } from 'react'
import { useStores } from '@/hooks/useStores'
import { SelectedStoreContext, type SelectedStoreContextValue } from '@/catalog/useSelectedStore'

export function SelectedStoreProvider({ children }: { children: ReactNode }) {
  const storesQuery = useStores()
  const stores = storesQuery.data ?? []
  const [selectedId, setSelectedId] = useState<number | null>(null)

  const selectedStore = stores.find((store) => store.id === selectedId) ?? stores[0] ?? null

  const value: SelectedStoreContextValue = {
    stores,
    selectedStore,
    selectStore: (store) => setSelectedId(store.id),
    isPending: storesQuery.isPending,
    isError: storesQuery.isError,
  }

  return <SelectedStoreContext.Provider value={value}>{children}</SelectedStoreContext.Provider>
}
