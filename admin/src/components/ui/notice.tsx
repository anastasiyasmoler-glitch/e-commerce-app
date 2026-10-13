import type { ReactNode } from 'react'
import { Info } from 'lucide-react'

export function Notice({
  children,
  tone = 'neutral',
}: {
  children: ReactNode
  tone?: 'neutral' | 'danger'
}) {
  return (
    <div className={`notice notice-${tone}`} style={{ marginBottom: 'var(--space-5)' }}>
      <Info size={18} aria-hidden />
      <div>{children}</div>
    </div>
  )
}
