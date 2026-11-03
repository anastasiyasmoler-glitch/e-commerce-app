import { Link } from 'react-router-dom'
import { SearchX } from 'lucide-react'
import { paths } from '@/paths'

export function NotFoundPage() {
  return (
    <div className="empty" style={{ paddingTop: 'var(--space-24)' }}>
      <SearchX aria-hidden strokeWidth={1.5} />
      <h2>Page not found</h2>
      <p className="muted" style={{ marginBottom: 'var(--space-6)' }}>
        This admin URL does not exist or was moved.
      </p>
      <Link className="btn btn-primary" to={paths.analytics}>
        Back to dashboard
      </Link>
    </div>
  )
}
