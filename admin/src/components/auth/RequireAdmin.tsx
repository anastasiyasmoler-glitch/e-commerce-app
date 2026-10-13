import { Navigate, Outlet } from 'react-router-dom'
import { isStaff } from '@/api/authApi'
import { useMe } from '@/hooks/useMe'
import { useSession } from '@/hooks/useSession'
import { paths } from '@/paths'

export function RequireAdmin() {
  const session = useSession()
  const me = useMe()

  if (!session.isFetched || (session.data && !me.isFetched)) {
    return null
  }

  if (!session.data || (me.data && !isStaff(me.data))) {
    return <Navigate to={paths.login} replace />
  }

  return <Outlet />
}
