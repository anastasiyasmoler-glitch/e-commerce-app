import type { ReindexStats } from '@/api/recommendationAdminApi'

export type ReindexFeedbackKind = 'success' | 'error'

export type ReindexFeedback = {
  kind: ReindexFeedbackKind
  message: string
}

export function reindexFeedbackFromStats(stats: ReindexStats): ReindexFeedback {
  const { processed, vectors_indexed, vector_failures } = stats

  if (processed === 0) {
    return {
      kind: 'error',
      message:
        'Reindex finished but the catalog projection is empty. Run the Recommendation Kafka consumer and ingest catalog events, then try again.',
    }
  }

  if (vectors_indexed === 0) {
    return {
      kind: 'error',
      message: `Reindex failed: 0 of ${processed} products indexed (${vector_failures} embedding failures). Check GEMINI_API_KEY, Qdrant, and recommendation logs.`,
    }
  }

  if (vector_failures > 0) {
    return {
      kind: 'success',
      message: `Reindex partially complete: ${vectors_indexed}/${processed} vectors updated, ${vector_failures} failed.`,
    }
  }

  return {
    kind: 'success',
    message: `Reindex complete: ${vectors_indexed} product vector${vectors_indexed === 1 ? '' : 's'} updated.`,
  }
}
