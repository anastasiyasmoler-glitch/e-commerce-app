import { createBrowserRouter } from 'react-router-dom'
import { RequireAuth } from './components/auth/RequireAuth'
import { StoreLayout } from './components/layout/StoreLayout'
import { HomePage } from './pages/HomePage'
import { paths } from './paths'
import { ProfilePage } from './pages/ProfilePage'

export const router = createBrowserRouter([
  {
    path: paths.home,
    element: <StoreLayout />,
    children: [
      { index: true, element: <HomePage /> },
      {
        element: <RequireAuth />,
        children: [{ path: 'profile', element: <ProfilePage /> }],
      },
    ],
  },
])
