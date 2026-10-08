import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { AdminLayout } from './components/layout/AdminLayout'
import { DashboardPage } from './pages/DashboardPage'
import { PlaceholderPage } from './pages/PlaceholderPage'

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<AdminLayout />}>
          <Route index element={<DashboardPage />} />
          <Route
            path="users"
            element={
              <PlaceholderPage
                title="Users"
                hint="Will load GET /api/admin/users from Auth (admin JWT)."
              />
            }
          />
          <Route
            path="products"
            element={<PlaceholderPage title="Products" hint="Catalog Filament owns store products, not this SPA." />}
          />
          <Route
            path="orders"
            element={<PlaceholderPage title="Orders" hint="Wired when Order service exists." />}
          />
          <Route
            path="acl"
            element={<PlaceholderPage title="Access Control" hint="Roles live in Auth (Spatie)." />}
          />
          <Route
            path="analytics"
            element={<PlaceholderPage title="Analytics" hint="Wired when Analytics service exists." />}
          />
          <Route
            path="notifications"
            element={
              <PlaceholderPage
                title="Notifications"
                hint="History: Notification GET /api/notifications after Auth /api/me."
              />
            }
          />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}
