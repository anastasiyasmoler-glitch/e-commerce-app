import { Navigate, Outlet } from 'react-router-dom'
import { isStaff } from '@/api/authApi'
import { LoadingScreen } from '@/components/ui/loading-screen'
import { useMe } from '@/hooks/useMe'
import { useSession } from '@/hooks/useSession'
import { paths } from '@/paths'

export function RequireAdmin() {
  const session = useSession()
  const me = useMe()

  if (!session.isFetched || (session.data && !me.isFetched)) {
    return <LoadingScreen />
  }

  if (!session.data || me.isError || !me.data || !isStaff(me.data)) {
    return <Navigate to={paths.login} replace />
  }

  return <Outlet />
}
