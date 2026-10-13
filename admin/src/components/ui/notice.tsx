import type { ReactNode } from 'react'
import { Info } from 'lucide-react'

export function Notice({ children }: { children: ReactNode }) {
  return (
    <div className="notice notice-neutral" style={{ marginBottom: 'var(--space-5)' }}>
      <Info size={18} aria-hidden />
      <div>{children}</div>
    </div>
  )
}
