import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { AuthProvider } from './auth/AuthProvider'
import { RequireAdmin } from './auth/RequireAdmin'
import { AdminLayout } from './components/layout/AdminLayout'
import { DashboardPage } from './pages/DashboardPage'
import { LoginPage } from './pages/LoginPage'
import { PlaceholderPage } from './pages/PlaceholderPage'
import { UsersPage } from './pages/UsersPage'

const queryClient = new QueryClient()

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <BrowserRouter>
          <Routes>
            <Route path="/login" element={<LoginPage />} />
            <Route element={<RequireAdmin />}>
              <Route element={<AdminLayout />}>
                <Route index element={<DashboardPage />} />
                <Route path="users" element={<UsersPage />} />
                <Route
                  path="acl"
                  element={<UsersPage />}
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
                  path="analytics"
                  element={<PlaceholderPage title="Analytics" hint="Wired when Analytics service exists." />}
                />
                <Route
                  path="notifications"
                  element={
                    <PlaceholderPage
                      title="Notifications"
                      hint="History will go Frontend/Admin → Auth → Notification."
                    />
                  }
                />
                <Route path="*" element={<Navigate to="/" replace />} />
              </Route>
            </Route>
          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </QueryClientProvider>
  )
}
