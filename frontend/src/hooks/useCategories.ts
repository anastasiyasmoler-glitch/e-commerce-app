import { useQuery } from '@tanstack/react-query'
import { catalogKeys, fetchCategories } from '@/api/catalogApi'

export function useCategories(storeId: number | undefined, parentId?: number | null) {
  return useQuery({
    queryKey: catalogKeys.categories(storeId, parentId),
    queryFn: () => fetchCategories(storeId!, parentId),
    enabled: storeId !== undefined,
  })
}
