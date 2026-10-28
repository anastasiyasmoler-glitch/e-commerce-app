import { createBrowserRouter } from 'react-router-dom'
import { RequireAuth } from './components/auth/RequireAuth'
import { StoreLayout } from './components/layout/StoreLayout'
import { CategoryPage } from './pages/CategoryPage'
import { HomePage } from './pages/HomePage'
import { ProductPage } from './pages/ProductPage'
import { paths } from './paths'
import { CartPage } from './pages/CartPage'
import { ChatPage } from './pages/ChatPage'
import { CheckoutPage } from './pages/CheckoutPage'
import { OrderDetailPage } from './pages/OrderDetailPage'
import { OrdersPage } from './pages/OrdersPage'
import { ProfilePage } from './pages/ProfilePage'
import { SearchPage } from './pages/SearchPage'

export const router = createBrowserRouter([
  {
    path: paths.home,
    element: <StoreLayout />,
    children: [
      { index: true, element: <HomePage /> },
      { path: 'categories', element: <CategoryPage /> },
      { path: 'categories/:slug', element: <CategoryPage /> },
      { path: 'products/:id', element: <ProductPage /> },
      { path: 'search', element: <SearchPage /> },
      { path: 'cart', element: <CartPage /> },
      {
        element: <RequireAuth />,
        children: [
          { path: 'profile', element: <ProfilePage /> },
          { path: 'assistant', element: <ChatPage /> },
          { path: 'checkout', element: <CheckoutPage /> },
          { path: 'orders', element: <OrdersPage /> },
          { path: 'orders/:id', element: <OrderDetailPage /> },
        ],
      },
    ],
  },
])
