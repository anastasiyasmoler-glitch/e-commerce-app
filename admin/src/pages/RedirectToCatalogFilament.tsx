import { useEffect } from 'react'
import { openCatalogAdmin } from '@/lib/catalogAdmin'

export function RedirectToCatalogFilament() {
  useEffect(() => {
    openCatalogAdmin()
  }, [])

  return <p className="muted">Opening Catalog Filament…</p>
}
