import { queryOptions, useQuery } from '@tanstack/react-query'
import { authKeys, restoreSession } from '@/api/authApi'

export const sessionQueryOptions = queryOptions({
  queryKey: authKeys.session,
  queryFn: restoreSession,
  staleTime: Infinity,
})

export function useSession() {
  return useQuery(sessionQueryOptions)
}
