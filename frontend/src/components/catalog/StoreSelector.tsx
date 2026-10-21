import { Store } from 'lucide-react'
import { useSelectedStore } from '@/catalog/SelectedStoreContext'

export function StoreSelector() {
  const { stores, selectedStore, selectStore, isPending } = useSelectedStore()

  if (isPending) {
    return (
      <div
        className="h-9 w-[min(100%,200px)] animate-pulse rounded-[6px] bg-subtle"
        aria-hidden
      />
    )
  }

  if (stores.length === 0) {
    return <span className="text-[13px] text-muted">No stores</span>
  }

  return (
    <label className="flex min-w-0 items-center gap-2">
      <Store className="size-4 shrink-0 text-muted" aria-hidden />
      <span className="hidden shrink-0 text-[12px] font-medium text-muted sm:inline">Store</span>
      <select
        className="h-9 max-w-[200px] min-w-0 flex-1 truncate rounded-[6px] border border-line bg-surface px-2.5 text-[13px] font-medium text-ink outline-none transition-colors focus:border-brand focus:ring-3 focus:ring-brand/14 sm:max-w-[220px]"
        value={selectedStore?.id ?? ''}
        onChange={(event) => {
          const id = Number(event.target.value)
          const store = stores.find((item) => item.id === id)
          if (store) {
            selectStore(store)
          }
        }}
        aria-label="Select store"
      >
        {stores.map((store) => (
          <option key={store.id} value={store.id}>
            {store.name}
          </option>
        ))}
      </select>
    </label>
  )
}
