import { Outlet } from 'react-router-dom'
import { AdminHeader } from './AdminHeader'
import { AdminSidebar } from './AdminSidebar'

export function AdminLayout() {
  return (
    <>
      <AdminHeader />
      <main>
        <div className="container">
          <nav className="breadcrumbs" aria-label="Breadcrumb">
            <span>Home</span>
            <span aria-hidden>/</span>
            <span className="breadcrumbs-current">Admin</span>
          </nav>
          <div className="admin-layout">
            <AdminSidebar />
            <div className="admin-main">
              <Outlet />
            </div>
          </div>
        </div>
      </main>
    </>
  )
}
