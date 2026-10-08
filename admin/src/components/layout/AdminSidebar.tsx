import {
  BarChart3,
  Bell,
  LayoutDashboard,
  Lock,
  Package,
  ShoppingCart,
  Users,
} from 'lucide-react'
import { NavLink } from 'react-router-dom'

const management = [
  { to: '/', label: 'Dashboard', icon: LayoutDashboard, end: true },
  { to: '/products', label: 'Products', icon: Package, end: false },
  { to: '/orders', label: 'Orders', icon: ShoppingCart, end: false },
  { to: '/users', label: 'Users', icon: Users, end: false },
  { to: '/acl', label: 'Access Control', icon: Lock, end: false },
]

const insights = [
  { to: '/analytics', label: 'Analytics', icon: BarChart3 },
  { to: '/notifications', label: 'Notifications', icon: Bell },
]

export function AdminSidebar() {
  return (
    <nav className="admin-sidebar" aria-label="Admin">
      <div className="admin-nav-title">Management</div>
      {management.map(({ to, label, icon: Icon, end }) => (
        <NavLink
          key={to}
          to={to}
          end={end}
          className={({ isActive }) =>
            isActive ? 'admin-nav-link active' : 'admin-nav-link'
          }
        >
          <Icon size={18} aria-hidden />
          {label}
        </NavLink>
      ))}
      <div className="admin-nav-title" style={{ marginTop: 12 }}>
        Insights
      </div>
      {insights.map(({ to, label, icon: Icon }) => (
        <NavLink
          key={to}
          to={to}
          className={({ isActive }) =>
            isActive ? 'admin-nav-link active' : 'admin-nav-link'
          }
        >
          <Icon size={18} aria-hidden />
          {label}
        </NavLink>
      ))}
    </nav>
  )
}
