import { Navigate, Outlet } from 'react-router-dom'
import { useMe } from '@/hooks/useMe'
import { paths } from '@/paths'

export function RequireRole({ role }: { role: string }) {
  const me = useMe()

  if (me.isFetched && me.data && !me.data.roles.includes(role)) {
    return <Navigate to={paths.analytics} replace />
  }

  return <Outlet />
}
