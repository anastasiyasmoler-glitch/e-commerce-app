import { useMutation, useQueryClient } from '@tanstack/react-query'
import { authKeys, me, register } from '@/api/authApi'

export function useRegister() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (values: Parameters<typeof register>[0]) => {
      await register(values)
      return me()
    },
    onSuccess: (profile) => {
      queryClient.setQueryData(authKeys.session, true)
      queryClient.setQueryData(authKeys.me, profile)
    },
  })
}
