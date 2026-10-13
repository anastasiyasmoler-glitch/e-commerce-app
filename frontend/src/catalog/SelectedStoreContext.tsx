import { createContext, useContext, useState, type ReactNode } from 'react'
import { useQuery } from '@tanstack/react-query'
import { catalogKeys, fetchStores, type CatalogStore } from '@/api/catalogApi'

type SelectedStoreContextValue = {
  stores: CatalogStore[]
  selectedStore: CatalogStore | null
  selectStore: (store: CatalogStore) => void
  isLoading: boolean
}

const SelectedStoreContext = createContext<SelectedStoreContextValue | null>(null)

export function SelectedStoreProvider({ children }: { children: ReactNode }) {
  const storesQuery = useQuery({ queryKey: catalogKeys.stores, queryFn: fetchStores })
  const stores = storesQuery.data ?? []
  const [selectedId, setSelectedId] = useState<number | null>(null)

  const selectedStore = stores.find((store) => store.id === selectedId) ?? stores[0] ?? null

  const value: SelectedStoreContextValue = {
    stores,
    selectedStore,
    selectStore: (store) => setSelectedId(store.id),
    isLoading: storesQuery.isLoading,
  }

  return <SelectedStoreContext.Provider value={value}>{children}</SelectedStoreContext.Provider>
}

export function useSelectedStore(): SelectedStoreContextValue {
  const ctx = useContext(SelectedStoreContext)
  if (ctx === null) {
    throw new Error('useSelectedStore must be used within SelectedStoreProvider')
  }
  return ctx
}
