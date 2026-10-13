import { useQuery } from '@tanstack/react-query'
import { authKeys, me } from '@/api/authApi'
import { useSession } from './useSession'

export function useMe() {
  const session = useSession()

  return useQuery({
    queryKey: authKeys.me,
    queryFn: me,
    enabled: session.data === true,
    staleTime: Infinity,
  })
}
