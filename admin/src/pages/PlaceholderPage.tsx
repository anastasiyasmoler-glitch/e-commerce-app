import { ExternalLink } from 'lucide-react'
import { usePlaceholderPage } from '@/hooks/usePlaceholderPage'
import { PageHeader } from '@/components/ui/page-header'

type PlaceholderPageProps = {
  title: string
  hint: string
  filamentPath?: string
}

export function PlaceholderPage({ title, hint, filamentPath = '/catalog-admin' }: PlaceholderPageProps) {
  const { openFilament } = usePlaceholderPage(filamentPath)

  return (
    <>
      <PageHeader title={title} />
      <p className="muted" style={{ marginBottom: 16 }}>
        {hint}
      </p>
      <button type="button" onClick={openFilament} className="btn btn-primary">
        Open in Catalog Filament
        <ExternalLink size={16} />
      </button>
    </>
  )
}
