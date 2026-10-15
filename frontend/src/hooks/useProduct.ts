import { useQuery } from '@tanstack/react-query'
import { catalogKeys, fetchProduct } from '@/api/catalogApi'

export function useProduct(storeId: number | undefined, id: number | undefined) {
  return useQuery({
    queryKey: catalogKeys.product(storeId, id),
    queryFn: () => fetchProduct(storeId!, id!),
    enabled: storeId !== undefined && id !== undefined && id > 0,
  })
}
