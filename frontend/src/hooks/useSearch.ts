import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { fetchSearch, searchKeys, type SearchParams } from '@/api/searchApi'

export function useSearch(
  storeId: number | undefined,
  params: SearchParams,
  enabled: boolean = true,
) {
  return useQuery({
    queryKey: searchKeys.query(storeId, params),
    queryFn: () => fetchSearch({ ...params, store_id: storeId! }),
    enabled: storeId !== undefined && enabled,
    placeholderData: keepPreviousData,
  })
}
