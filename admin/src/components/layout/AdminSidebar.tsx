import {
  BarChart3,
  Bell,
  Lock,
  Package,
  ShoppingCart,
  Store,
  Users,
  Folder,
} from 'lucide-react'
import { NavLink } from 'react-router-dom'
import { useMe } from '@/hooks/useMe'
import { paths } from '@/paths'

type NavItem = {
  to: string
  label: string
  icon: typeof Package
  tag?: string
}

const catalog: NavItem[] = [
  { to: paths.products, label: 'Products', icon: Package },
  { to: paths.categories, label: 'Categories', icon: Folder },
  { to: paths.stores, label: 'Stores', icon: Store },
]

const sales: NavItem[] = [{ to: paths.orders, label: 'Orders', icon: ShoppingCart, tag: 'Analyst' }]

const insights: NavItem[] = [
  { to: paths.analytics, label: 'Analytics', icon: BarChart3, tag: 'Analyst' },
  { to: paths.notifications, label: 'Notifications', icon: Bell, tag: 'Analyst' },
]

const access: NavItem[] = [
  { to: paths.users, label: 'Users', icon: Users },
  { to: paths.access, label: 'Access control', icon: Lock },
]

function NavLinks({ items }: { items: NavItem[] }) {
  return items.map(({ to, label, icon: Icon, tag }) => (
    <NavLink
      key={to}
      to={to}
      className={({ isActive }) => (isActive ? 'admin-nav-link active' : 'admin-nav-link')}
    >
      <Icon size={18} aria-hidden />
      {label}
      {tag ? <span className="tag">{tag}</span> : null}
    </NavLink>
  ))
}

export function AdminSidebar() {
  const me = useMe()
  const isAdmin = Boolean(me.data?.roles.includes('admin'))

  return (
    <nav className="admin-sidebar" aria-label="Admin navigation">
      <div className="admin-nav-title">Catalog</div>
      <NavLinks items={catalog} />
      <div className="admin-nav-title">Sales</div>
      <NavLinks items={sales} />
      <div className="admin-nav-title">Insights</div>
      <NavLinks items={insights} />
      {isAdmin ? (
        <>
          <div className="admin-nav-title">Access</div>
          <NavLinks items={access} />
        </>
      ) : null}
    </nav>
  )
}
