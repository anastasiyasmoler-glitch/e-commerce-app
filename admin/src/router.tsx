import { createBrowserRouter, Navigate } from 'react-router-dom'
import { RequireAdmin } from '@/components/auth/RequireAdmin'
import { RequireRole } from '@/components/auth/RequireRole'
import { AdminLayout } from '@/components/layout/AdminLayout'
import { AnalyticsPage } from '@/pages/AnalyticsPage'
import { LoginPage } from '@/pages/LoginPage'
import { NotificationsPage } from '@/pages/NotificationsPage'
import { OrdersPage } from '@/pages/OrdersPage'
import { PlaceholderPage } from '@/pages/PlaceholderPage'
import { UsersPage } from '@/pages/UsersPage'
import { paths } from './paths'

const segment = (path: string) => path.replace(/^\//, '')

export const router = createBrowserRouter([
  {
    path: '/',
    children: [
      { path: segment(paths.login), element: <LoginPage /> },
      {
        element: <RequireAdmin />,
        children: [
          {
            element: <AdminLayout />,
            children: [
              { index: true, element: <Navigate to={paths.analytics} replace /> },
              { path: segment(paths.orders), element: <OrdersPage /> },
              { path: segment(paths.analytics), element: <AnalyticsPage /> },
              { path: segment(paths.notifications), element: <NotificationsPage /> },
              {
                element: <RequireRole role="admin" />,
                children: [
                  { path: segment(paths.users), element: <UsersPage /> },
                ],
              },
              {
                path: segment(paths.products),
                element: (
                  <PlaceholderPage
                    title="Products"
                    hint="Catalog service owns products and stock via its Filament panel."
                    filamentPath="/catalog-admin/products"
                  />
                ),
              },
              {
                path: segment(paths.categories),
                element: (
                  <PlaceholderPage
                    title="Categories"
                    hint="Catalog service owns categories via its Filament panel."
                    filamentPath="/catalog-admin/categories"
                  />
                ),
              },
              {
                path: segment(paths.stores),
                element: (
                  <PlaceholderPage
                    title="Stores"
                    hint="Catalog service owns stores via its Filament panel."
                    filamentPath="/catalog-admin/stores"
                  />
                ),
              },
              { path: '*', element: <Navigate to={paths.analytics} replace /> },
            ],
          },
        ],
      },
    ],
  },
])
