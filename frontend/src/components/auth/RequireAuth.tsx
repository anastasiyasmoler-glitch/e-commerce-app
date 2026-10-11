import { Outlet, useNavigate } from 'react-router-dom'
import { useSession } from '@/hooks/useSession'
import { paths } from '@/paths'
import { AuthModal } from './AuthModal'

export function RequireAuth() {
  const session = useSession()
  const navigate = useNavigate()

  if (!session.isFetched) {
    return null
  }

  if (!session.data) {
    return (
      <AuthModal
        onClose={() => navigate(paths.home, { replace: true })}
        onSuccess={() => undefined}
      />
    )
  }

  return <Outlet />
}
