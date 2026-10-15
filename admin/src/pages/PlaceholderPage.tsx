import { ExternalLink } from 'lucide-react'
import { getAccessToken } from '@/api/http'
import { PageHeader } from '@/components/ui/page-header'

type PlaceholderPageProps = {
  title: string
  hint: string
  filamentPath?: string
}

const gatewayUrl = (import.meta.env.VITE_AUTH_URL as string | undefined) ?? 'https://localhost'

export function PlaceholderPage({ title, hint, filamentPath = '/catalog-admin' }: PlaceholderPageProps) {
  function openFilament() {
    const token = getAccessToken()
    const url = new URL(`${gatewayUrl}${filamentPath}`)
    if (token) {
      url.searchParams.set('token', token)
    }
    window.open(url.toString(), '_blank', 'noopener,noreferrer')
  }

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
