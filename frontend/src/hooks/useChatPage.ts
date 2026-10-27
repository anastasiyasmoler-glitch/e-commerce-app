import { useSelectedStore } from '@/catalog/useSelectedStore'

export type ChatPageStatus = 'loading' | 'no-store' | 'ready'

export function useChatPage(): {
  status: ChatPageStatus
  storeId?: number
  storeName: string
} {
  const { selectedStore, isPending: storesPending } = useSelectedStore()
  const storeId = selectedStore?.id
  const storeName = selectedStore?.name ?? ''

  if (storesPending) {
    return { status: 'loading', storeName }
  }

  if (storeId === undefined) {
    return { status: 'no-store', storeName }
  }

  return { status: 'ready', storeId, storeName }
}
