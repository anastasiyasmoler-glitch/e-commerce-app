import { Sparkles } from 'lucide-react'
import { useRecommendationsPage } from '@/hooks/useRecommendationsPage'
import { PageHeader } from '@/components/ui/page-header'
import { Button } from '@/components/ui/button'

export function RecommendationsPage() {
  const page = useRecommendationsPage()

  return (
    <>
      <PageHeader title="AI recommendations" />
      <p className="muted" style={{ marginBottom: 16, maxWidth: 560 }}>
        Rebuild Qdrant vectors from the local catalog projection (Kafka ingest). Use after a catalog
        re-seed or when chat search returns no matches despite products in the database. Calls Gemini
        embed for each row — may take a minute on large catalogs.
      </p>
      <Button type="button" disabled={page.reindexPending} onClick={() => page.runReindex()}>
        <Sparkles size={16} aria-hidden />
        {page.reindexPending ? 'Reindexing…' : 'Reindex product vectors'}
      </Button>
    </>
  )
}
