import { useMutation, useQueryClient } from '@tanstack/react-query'
import { authKeys, register } from '@/api/authApi'

export function useRegister() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: register,
    onSuccess: async () => {
      queryClient.setQueryData(authKeys.session, true)
      await queryClient.invalidateQueries({ queryKey: authKeys.me })
    },
  })
}
