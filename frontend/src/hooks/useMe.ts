import { useQuery } from '@tanstack/react-query'
import { authKeys, me } from '@/api/authApi'
import { useRestoreSession } from './useRestoreSession'

export function useMe() {
  const session = useRestoreSession()

  return useQuery({
    queryKey: authKeys.me,
    queryFn: me,
    enabled: session.isSuccess && session.data === true,
  })
}
