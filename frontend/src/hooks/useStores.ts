import { useQuery } from '@tanstack/react-query'
import { catalogKeys, fetchStores } from '@/api/catalogApi'

export function useStores() {
  return useQuery({
    queryKey: catalogKeys.stores,
    queryFn: fetchStores,
  })
}
