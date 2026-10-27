import { zodResolver } from '@hookform/resolvers/zod'
import { useForm } from 'react-hook-form'
import { useNavigate } from 'react-router-dom'
import { useLogout } from '@/hooks/useLogout'
import { useDeleteProfile, useProfile, useUpdatePassword, useUpdateProfile } from '@/hooks/useProfile'
import { paths } from '@/paths'
import {
  deleteProfileSchema,
  updatePasswordSchema,
  updateProfileSchema,
  type DeleteProfileValues,
  type UpdatePasswordValues,
  type UpdateProfileValues,
} from '@/lib/validators/auth'

export function useProfilePage() {
  const profile = useProfile()
  const me = profile.data
  const logout = useLogout()
  const updateProfile = useUpdateProfile()
  const changePassword = useUpdatePassword()
  const removeAccount = useDeleteProfile()
  const navigate = useNavigate()

  const detailsForm = useForm<UpdateProfileValues>({
    resolver: zodResolver(updateProfileSchema),
    values: {
      name: me?.name ?? '',
      phone: me?.phone ?? '',
      email: me?.email ?? '',
    },
  })

  const passwordForm = useForm<UpdatePasswordValues>({
    resolver: zodResolver(updatePasswordSchema),
    defaultValues: { current_password: '', password: '', password_confirmation: '' },
  })

  const deleteForm = useForm<DeleteProfileValues>({
    resolver: zodResolver(deleteProfileSchema),
    defaultValues: { password: '' },
  })

  function onSignOut() {
    logout.mutate(undefined, {
      onSettled: () => {
        navigate(paths.home)
      },
    })
  }

  async function onSaveDetails(values: UpdateProfileValues) {
    try {
      await updateProfile.mutateAsync(values)
    } catch {
      /* mutation.error surfaced in UI */
    }
  }

  async function onChangePassword(values: UpdatePasswordValues) {
    try {
      await changePassword.mutateAsync(values)
      passwordForm.reset()
    } catch {
      /* mutation.error */
    }
  }

  async function onDelete(values: DeleteProfileValues) {
    try {
      await removeAccount.mutateAsync(values)
      navigate(paths.home)
    } catch {
      /* mutation.error */
    }
  }

  const detailsLoading = profile.isPending && !me
  const role = me?.roles[0] ?? 'Customer'

  return {
    me,
    role,
    detailsLoading,
    detailsForm,
    passwordForm,
    deleteForm,
    logout,
    updateProfile,
    changePassword,
    removeAccount,
    onSignOut,
    onSaveDetails,
    onChangePassword,
    onDelete,
  }
}
