import { createBrowserRouter } from 'react-router-dom'
import { RequireAuth } from './components/auth/RequireAuth'
import { StoreLayout } from './components/layout/StoreLayout'
import { CategoryPage } from './pages/CategoryPage'
import { HomePage } from './pages/HomePage'
import { ProductPage } from './pages/ProductPage'
import { paths } from './paths'
import { ProfilePage } from './pages/ProfilePage'

export const router = createBrowserRouter([
  {
    path: paths.home,
    element: <StoreLayout />,
    children: [
      { index: true, element: <HomePage /> },
      { path: 'categories', element: <CategoryPage /> },
      { path: 'categories/:slug', element: <CategoryPage /> },
      { path: 'products/:id', element: <ProductPage /> },
      {
        element: <RequireAuth />,
        children: [{ path: 'profile', element: <ProfilePage /> }],
      },
    ],
  },
])
