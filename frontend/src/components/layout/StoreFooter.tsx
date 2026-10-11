import { Link } from 'react-router-dom'
import { Container } from '@/components/ui/container'
import { paths } from '@/paths'

export function StoreFooter() {
  return (
    <footer className="mt-16 border-t border-line bg-subtle">
      <Container className="py-10">
        <div className="flex flex-wrap items-start justify-between gap-8">
          <div>
            <Link to={paths.home} className="font-display text-[22px] font-medium">
              Domo<span className="text-brand">.</span>
            </Link>
            <p className="mt-2 max-w-xs text-[14px] text-muted">Furniture and home goods from independent stores.</p>
          </div>
          <nav className="flex flex-wrap gap-6 text-[14px] font-medium" aria-label="Footer">
            <span>Catalog</span>
            <span>Stores</span>
            <span>AI Assistant</span>
            <span>My orders</span>
          </nav>
        </div>
        <div className="mt-8 text-[13px] text-muted">© 2026 Domo</div>
      </Container>
    </footer>
  )
}
