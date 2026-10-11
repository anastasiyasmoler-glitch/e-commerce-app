import { Navigate, Outlet } from 'react-router-dom'
import { isAdmin } from './session'
import { useAuth } from './useAuth'

export function RequireAdmin() {
  const { user } = useAuth()
  if (!isAdmin(user)) {
    return <Navigate to="/login" replace />
  }
  return <Outlet />
}
