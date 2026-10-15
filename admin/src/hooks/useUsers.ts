import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { adminKeys, fetchUsers, setUserRole } from '@/api/adminApi'

export function useUsers() {
  return useQuery({
    queryKey: adminKeys.users,
    queryFn: fetchUsers,
  })
}

export function useSetUserRole() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ id, next }: { id: number; next: boolean }) =>
      setUserRole(id, next ? 'analyst' : 'customer'),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: adminKeys.users }),
  })
}
