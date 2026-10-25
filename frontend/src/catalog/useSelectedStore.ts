import { createContext, useContext } from 'react'
import type { CatalogStore } from '@/api/catalogApi'

export type SelectedStoreContextValue = {
  stores: CatalogStore[]
  selectedStore: CatalogStore | null
  selectStore: (store: CatalogStore) => void
  isPending: boolean
  isError: boolean
}

export const SelectedStoreContext = createContext<SelectedStoreContextValue | null>(null)

export function useSelectedStore(): SelectedStoreContextValue {
  const ctx = useContext(SelectedStoreContext)
  if (ctx === null) {
    throw new Error('useSelectedStore must be used within SelectedStoreProvider')
  }
  return ctx
}
