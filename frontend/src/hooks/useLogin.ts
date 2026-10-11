import { useMutation, useQueryClient } from '@tanstack/react-query'
import { authKeys, login, me } from '@/api/authApi'

export function useLogin() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (values: Parameters<typeof login>[0]) => {
      await login(values)
      return me()
    },
    onSuccess: (profile) => {
      queryClient.setQueryData(authKeys.session, true)
      queryClient.setQueryData(authKeys.me, profile)
    },
  })
}
