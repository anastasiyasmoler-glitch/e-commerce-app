import { useMutation, useQueryClient } from '@tanstack/react-query'
import { authKeys, login } from '@/api/authApi'

export function useLogin() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: login,
    onSuccess: async () => {
      queryClient.setQueryData(authKeys.session, true)
      await queryClient.invalidateQueries({ queryKey: authKeys.me })
    },
  })
}
