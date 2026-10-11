import { useQuery } from '@tanstack/react-query'
import { authKeys, refresh } from '@/api/authApi'
import { setAccessToken } from '@/api/http'

export function useRestoreSession() {
  return useQuery({
    queryKey: authKeys.session,
    queryFn: async () => {
      try {
        await refresh()
        return true
      } catch {
        setAccessToken(null)
        return false
      }
    },
    staleTime: Infinity,
  })
}
