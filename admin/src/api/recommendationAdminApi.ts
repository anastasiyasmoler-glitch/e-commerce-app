import { http } from './http'

export type ReindexStats = {
  processed: number
  vectors_indexed: number
  vector_failures: number
}

export async function triggerRecommendationReindex(
  storeId?: number,
): Promise<ReindexStats> {
  const { data } = await http.post<{ data: ReindexStats }>('/api/recommendations/reindex', {
    store_id: storeId ?? undefined,
  })

  return data.data
}
