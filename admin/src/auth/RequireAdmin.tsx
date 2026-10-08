import { Navigate, Outlet } from 'react-router-dom'
import { isAdmin } from './session'
import { useAuth } from './AuthProvider'

export function RequireAdmin() {
  const { user } = useAuth()
  if (!isAdmin(user)) {
    return <Navigate to="/login" replace />
  }
  return <Outlet />
}
