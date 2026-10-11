import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import toast from 'react-hot-toast'
import { authKeys } from '@/api/authApi'
import { deleteProfile, getProfile, updatePassword, updateProfile } from '@/api/profileApi'
import { apiErrorMessage, setAccessToken } from '@/api/http'
import type { DeleteProfileValues, UpdatePasswordValues, UpdateProfileValues } from '@/lib/validators/auth'
import { useSession } from './useSession'

export function useProfile() {
  const session = useSession()

  return useQuery({
    queryKey: authKeys.me,
    queryFn: getProfile,
    enabled: session.data === true,
    staleTime: Infinity,
  })
}

export function useUpdateProfile() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (values: UpdateProfileValues) => updateProfile(values),
    onSuccess: (profile) => {
      queryClient.setQueryData(authKeys.me, profile)
      toast.success('Profile saved.')
    },
    onError: (error) => {
      toast.error(apiErrorMessage(error))
    },
  })
}

export function useUpdatePassword() {
  return useMutation({
    mutationFn: (values: UpdatePasswordValues) => updatePassword(values),
    onSuccess: () => {
      toast.success('Password updated.')
    },
    onError: (error) => {
      toast.error(apiErrorMessage(error))
    },
  })
}

export function useDeleteProfile() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (values: DeleteProfileValues) => deleteProfile(values),
    onSuccess: () => {
      setAccessToken(null)
      queryClient.setQueryData(authKeys.session, false)
      queryClient.removeQueries({ queryKey: authKeys.me })
      toast.success('Account deleted.')
    },
    onError: (error) => {
      toast.error(apiErrorMessage(error))
    },
  })
}
