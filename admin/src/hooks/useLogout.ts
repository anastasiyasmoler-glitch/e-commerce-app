import { useMutation, useQueryClient } from '@tanstack/react-query'
import { authKeys, logout } from '@/api/authApi'
import { setAccessToken } from '@/api/http'

export function useLogout() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: logout,
    onSettled: async () => {
      setAccessToken(null)
      queryClient.setQueryData(authKeys.session, false)
      queryClient.removeQueries({ queryKey: authKeys.me })
    },
  })
}
