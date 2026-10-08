import { Bell, LogOut, Shield, User } from 'lucide-react'
import { Link } from 'react-router-dom'
import { useAuth } from '../../auth/AuthProvider'

export function AdminHeader() {
  const { user, logout } = useAuth()

  return (
    <>
      <div className="topbar">
        <div className="container topbar-inner">
          <span className="topbar-item">
            <Shield size={16} aria-hidden />
            Admin Panel
          </span>
        </div>
      </div>
      <header className="header">
        <div className="container header-inner">
          <Link to="/" className="logo">
            <span className="logo-mark">D</span>
            Domo Admin
          </Link>
          <nav className="header-actions" aria-label="Account">
            <button type="button" className="header-action" aria-label="Alerts">
              <span style={{ position: 'relative' }}>
                <Bell size={22} />
                <span className="header-action-badge">3</span>
              </span>
              <span>Alerts</span>
            </button>
            <span className="header-action">
              <User size={22} />
              <span>{user?.name ?? 'Admin'}</span>
            </span>
            <button type="button" className="header-action" aria-label="Log out" onClick={() => void logout()}>
              <LogOut size={22} />
              <span>Exit</span>
            </button>
          </nav>
        </div>
      </header>
    </>
  )
}
