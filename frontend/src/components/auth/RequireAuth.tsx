import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { PageLoading } from '@/components/ui/loading-screen'
import { useSession } from '@/hooks/useSession'
import { paths } from '@/paths'

export function RequireAuth() {
  const session = useSession()
  const location = useLocation()

  if (!session.isFetched) {
    return <PageLoading />
  }

  if (!session.data) {
    const from = `${location.pathname}${location.search}${location.hash}`

    return (
      <Navigate
        to={paths.home}
        replace
        state={{ auth: 'login', from }}
      />
    )
  }

  return <Outlet />
}
