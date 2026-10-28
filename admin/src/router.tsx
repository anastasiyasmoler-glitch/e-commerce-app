import { createBrowserRouter, Navigate } from 'react-router-dom'
import { RequireAdmin } from '@/components/auth/RequireAdmin'
import { RequireRole } from '@/components/auth/RequireRole'
import { AdminLayout } from '@/components/layout/AdminLayout'
import { AnalyticsPage } from '@/pages/AnalyticsPage'
import { LoginPage } from '@/pages/LoginPage'
import { NotificationsPage } from '@/pages/NotificationsPage'
import { OrdersPage } from '@/pages/OrdersPage'
import { RedirectToCatalogFilament } from '@/pages/RedirectToCatalogFilament'
import { RecommendationsPage } from '@/pages/RecommendationsPage'
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
                  { path: segment(paths.recommendations), element: <RecommendationsPage /> },
                ],
              },
              { path: 'catalog', element: <RedirectToCatalogFilament /> },
              { path: 'products', element: <RedirectToCatalogFilament /> },
              { path: 'categories', element: <RedirectToCatalogFilament /> },
              { path: 'stores', element: <RedirectToCatalogFilament /> },
              { path: '*', element: <Navigate to={paths.analytics} replace /> },
            ],
          },
        ],
      },
    ],
  },
])
