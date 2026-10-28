import { Package } from 'lucide-react'
import { buildCatalogAdminUrl, openCatalogAdmin } from '@/lib/catalogAdmin'

export function CatalogFilamentLink() {
  return (
    <a
      href={buildCatalogAdminUrl()}
      className="admin-nav-link"
      onClick={(event) => {
        event.preventDefault()
        openCatalogAdmin()
      }}
    >
      <Package size={18} aria-hidden />
      Catalog
    </a>
  )
}
