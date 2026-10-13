import { createBrowserRouter, Navigate } from 'react-router-dom'
import { RequireAdmin } from '@/components/auth/RequireAdmin'
import { RequireRole } from '@/components/auth/RequireRole'
import { AdminLayout } from '@/components/layout/AdminLayout'
import { AccessControlPage } from '@/pages/AccessControlPage'
import { AnalyticsPage } from '@/pages/AnalyticsPage'
import { LoginPage } from '@/pages/LoginPage'
import { NotificationsPage } from '@/pages/NotificationsPage'
import { OrdersPage } from '@/pages/OrdersPage'
import { PlaceholderPage } from '@/pages/PlaceholderPage'
import { UsersPage } from '@/pages/UsersPage'
import { paths } from './paths'

export const router = createBrowserRouter([
  { path: paths.login, element: <LoginPage /> },
  {
    element: <RequireAdmin />,
    children: [
      {
        element: <AdminLayout />,
        children: [
          { index: true, element: <Navigate to={paths.analytics} replace /> },
          { path: paths.orders, element: <OrdersPage /> },
          { path: paths.analytics, element: <AnalyticsPage /> },
          { path: paths.notifications, element: <NotificationsPage /> },
          {
            element: <RequireRole role="admin" />,
            children: [
              { path: paths.users, element: <UsersPage /> },
              { path: paths.access, element: <AccessControlPage /> },
            ],
          },
          {
            path: paths.products,
            element: (
              <PlaceholderPage title="Products" hint="Filament in Catalog owns this." />
            ),
          },
          {
            path: paths.categories,
            element: (
              <PlaceholderPage title="Categories" hint="Filament in Catalog owns this." />
            ),
          },
          {
            path: paths.stores,
            element: <PlaceholderPage title="Stores" hint="Filament in Catalog owns this." />,
          },
          { path: '*', element: <Navigate to={paths.analytics} replace /> },
        ],
      },
    ],
  },
])
