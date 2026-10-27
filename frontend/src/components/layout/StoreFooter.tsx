import { Link } from 'react-router-dom'
import { Container } from '@/components/ui/container'
import { paths } from '@/paths'

export function StoreFooter() {
  return (
    <footer className="mt-24 border-t border-line bg-surface pt-12 pb-8">
      <Container>
        <div className="flex flex-wrap items-start justify-between gap-8">
          <div>
            <Link
              to={paths.home}
              className="font-display text-[28px] leading-none font-semibold tracking-[-0.03em] text-brand"
            >
              Domo<i className="not-italic text-accent">.</i>
            </Link>
            <p className="mt-2 max-w-[300px] text-[13px] text-muted">
              Furniture and home goods from independent stores.
            </p>
          </div>
          <nav className="flex flex-wrap gap-8 text-[13px] font-medium" aria-label="Footer">
            <Link to={paths.catalog} className="hover:text-brand">
              Catalog
            </Link>
            <span>Stores</span>
            <Link to={paths.assistant} className="hover:text-brand">
              AI Assistant
            </Link>
            <span>My orders</span>
          </nav>
        </div>
        <div className="mt-8 border-t border-line pt-5 text-[12px] text-muted">© 2026 Domo</div>
      </Container>
    </footer>
  )
}
