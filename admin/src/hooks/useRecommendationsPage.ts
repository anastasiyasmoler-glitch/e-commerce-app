import { useMutation } from '@tanstack/react-query'
import { triggerRecommendationReindex } from '@/api/recommendationAdminApi'
import { apiErrorMessage } from '@/api/http'
import toast from 'react-hot-toast'

export function useRecommendationsPage() {
  const reindex = useMutation({
    mutationFn: (storeId?: number) => triggerRecommendationReindex(storeId),
    onSuccess: (stats) => {
      toast.success(
        `Reindex finished: ${stats.vectors_indexed}/${stats.processed} vectors updated (${stats.vector_failures} failures).`,
      )
    },
    onError: (error) => {
      toast.error(apiErrorMessage(error))
    },
  })

  return {
    reindexPending: reindex.isPending,
    runReindex: (storeId?: number) => reindex.mutate(storeId),
  }
}
