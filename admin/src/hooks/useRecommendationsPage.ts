import { useMutation } from '@tanstack/react-query'
import { useState } from 'react'
import toast from 'react-hot-toast'
import { triggerRecommendationReindex } from '@/api/recommendationAdminApi'
import { apiErrorMessage } from '@/api/http'
import {
  reindexFeedbackFromStats,
  type ReindexFeedback,
} from '@/lib/recommendation/reindexFeedback'

const REINDEX_TOAST_ID = 'recommendation-reindex'

export function useRecommendationsPage() {
  const [lastFeedback, setLastFeedback] = useState<ReindexFeedback | null>(null)

  const reindex = useMutation({
    mutationFn: (storeId?: number) => triggerRecommendationReindex(storeId),
  })

  function runReindex(storeId?: number) {
    setLastFeedback(null)
    toast.loading('Reindexing product vectors… This can take several minutes.', {
      id: REINDEX_TOAST_ID,
      duration: Infinity,
    })

    reindex.mutate(storeId, {
      onSuccess: (stats) => {
        toast.dismiss(REINDEX_TOAST_ID)
        const feedback = reindexFeedbackFromStats(stats)
        setLastFeedback(feedback)
        if (feedback.kind === 'success') {
          toast.success(feedback.message, { duration: 7000 })
        } else {
          toast.error(feedback.message, { duration: 9000 })
        }
      },
      onError: (error) => {
        toast.dismiss(REINDEX_TOAST_ID)
        const message = apiErrorMessage(error)
        const feedback: ReindexFeedback = { kind: 'error', message }
        setLastFeedback(feedback)
        toast.error(message, { duration: 9000 })
      },
    })
  }

  return {
    reindexPending: reindex.isPending,
    lastFeedback,
    runReindex,
  }
}
