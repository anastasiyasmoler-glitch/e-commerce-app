import type { ReactNode } from 'react'

type PageHeaderProps = {
  title: string
  actions?: ReactNode
}

export function PageHeader({ title, actions }: PageHeaderProps) {
  return (
    <div className="admin-header">
      <h1 className="admin-title">{title}</h1>
      {actions ? <div className="admin-actions">{actions}</div> : null}
    </div>
  )
}
