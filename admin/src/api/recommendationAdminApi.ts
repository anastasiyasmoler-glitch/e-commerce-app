import { http } from './http'

export type ReindexStats = {
  processed: number
  vectors_indexed: number
  vector_failures: number
}

function parseReindexStats(
  status: number,
  payload: unknown,
): ReindexStats {
  const wrapped = payload as { data?: ReindexStats } | null | undefined
  const stats = wrapped?.data

  if (
    stats &&
    typeof stats.processed === 'number' &&
    typeof stats.vectors_indexed === 'number' &&
    typeof stats.vector_failures === 'number'
  ) {
    return stats
  }

  if (status === 204 || payload === '' || payload == null) {
    throw new Error(
      'Reindex returned an empty response (often HTTP 204). In DevTools check the POST request, not only OPTIONS. Restart auth nginx if the POST never reaches recommendation.',
    )
  }

  throw new Error('Reindex response is missing stats JSON.')
}

export async function triggerRecommendationReindex(
  storeId?: number,
): Promise<ReindexStats> {
  const body = storeId != null ? { store_id: storeId } : {}
  const response = await http.post<{ data: ReindexStats }>(
    '/api/recommendations/reindex',
    body,
    { timeout: 600_000 },
  )

  return parseReindexStats(response.status, response.data)
}
