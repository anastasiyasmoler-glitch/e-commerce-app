import { Outlet } from 'react-router-dom'
import { AdminBar } from './AdminBar'
import { AdminSidebar } from './AdminSidebar'

export function AdminLayout() {
  return (
    <>
      <AdminBar />
      <main>
        <div className="container">
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
