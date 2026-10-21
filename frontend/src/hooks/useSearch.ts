import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { fetchSearch, searchKeys, type SearchParams } from '@/api/searchApi'

export function useSearch(storeId: number | undefined, params: SearchParams) {
  return useQuery({
    queryKey: searchKeys.query(storeId, params),
    queryFn: () => fetchSearch({ ...params, store_id: storeId! }),
    enabled: storeId !== undefined,
    placeholderData: keepPreviousData,
  })
}
