import { zodResolver } from '@hookform/resolvers/zod'
import { LogOut, Package, Sparkles, Trash2, User } from 'lucide-react'
import { Controller, useForm } from 'react-hook-form'
import { Link, useNavigate } from 'react-router-dom'
import { formErrorMessage } from '@/lib/form'
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
import { Badge } from '@/components/ui/badge'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Container } from '@/components/ui/container'
import { Field } from '@/components/ui/field'
import { FormError } from '@/components/ui/form-error'
import { PhoneField } from '@/components/ui/phone-field'
import { Separator } from '@/components/ui/separator'

export function ProfilePage() {
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
      /* mutation.error */
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

  const role = me?.roles[0] ?? 'Customer'

  return (
    <Container className="py-8">
      <Breadcrumb className="mb-4">
        <BreadcrumbList>
          <BreadcrumbItem>
            <BreadcrumbLink asChild>
              <Link to={paths.home}>Home</Link>
            </BreadcrumbLink>
          </BreadcrumbItem>
          <BreadcrumbSeparator />
          <BreadcrumbItem>
            <BreadcrumbPage>Profile</BreadcrumbPage>
          </BreadcrumbItem>
        </BreadcrumbList>
      </Breadcrumb>
      <div className="mb-8 flex flex-wrap items-center justify-between gap-4">
        <h1 className="font-display text-[36px] font-medium">Profile</h1>
        <Button type="button" variant="outline" className="h-11 rounded-sm" onClick={onSignOut} disabled={logout.isPending}>
          <LogOut />
          {logout.isPending ? 'Signing out…' : 'Sign out'}
        </Button>
      </div>
      <div className="grid gap-10 lg:grid-cols-[240px_1fr]">
        <nav className="flex flex-col gap-1">
          <Button variant="secondary" className="justify-start" type="button">
            <User /> Profile
          </Button>
          <Button variant="ghost" className="justify-start" type="button">
            <Package /> My orders
          </Button>
          <Button variant="ghost" className="justify-start" type="button">
            <Sparkles /> AI Assistant
          </Button>
          <Separator className="my-2" />
          <Button
            type="button"
            variant="ghost"
            className="justify-start"
            onClick={onSignOut}
            disabled={logout.isPending}
          >
            <LogOut /> Sign out
          </Button>
        </nav>
        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Personal details</CardTitle>
              <CardDescription className="flex items-center gap-2">
                Role: <Badge variant="secondary">{role}</Badge>
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={detailsForm.handleSubmit(onSaveDetails)}>
                <FormError message={formErrorMessage(updateProfile.error, detailsForm.formState.errors)} />
                <div className="grid gap-4 sm:grid-cols-2">
                  <Field
                    id="profile-name"
                    label="Full name"
                    autoComplete="name"
                    error={detailsForm.formState.errors.name?.message}
                    {...detailsForm.register('name')}
                  />
                  <Controller
                    control={detailsForm.control}
                    name="phone"
                    render={({ field, fieldState }) => (
                      <PhoneField
                        id="profile-phone"
                        label="Phone"
                        value={field.value}
                        onChange={field.onChange}
                        onBlur={field.onBlur}
                        name={field.name}
                        error={fieldState.error?.message}
                      />
                    )}
                  />
                </div>
                <Field
                  id="profile-email"
                  label="Email"
                  type="email"
                  autoComplete="email"
                  error={detailsForm.formState.errors.email?.message}
                  {...detailsForm.register('email')}
                />
                <Button type="submit" className="h-11 rounded-sm" disabled={updateProfile.isPending}>
                  {updateProfile.isPending ? 'Saving…' : 'Save changes'}
                </Button>
              </form>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>Password</CardTitle>
              <CardDescription>Use at least 8 characters.</CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={passwordForm.handleSubmit(onChangePassword)}>
                <FormError message={formErrorMessage(changePassword.error, passwordForm.formState.errors)} />
                <Field
                  id="profile-current-password"
                  label="Current password"
                  type="password"
                  autoComplete="current-password"
                  placeholder="••••••••"
                  error={passwordForm.formState.errors.current_password?.message}
                  {...passwordForm.register('current_password')}
                />
                <Field
                  id="profile-new-password"
                  label="New password"
                  type="password"
                  autoComplete="new-password"
                  placeholder="••••••••"
                  error={passwordForm.formState.errors.password?.message}
                  {...passwordForm.register('password')}
                />
                <Field
                  id="profile-confirm-password"
                  label="Confirm new password"
                  type="password"
                  autoComplete="new-password"
                  placeholder="••••••••"
                  error={passwordForm.formState.errors.password_confirmation?.message}
                  {...passwordForm.register('password_confirmation')}
                />
                <Button type="submit" variant="outline" className="h-11 rounded-sm" disabled={changePassword.isPending}>
                  {changePassword.isPending ? 'Updating…' : 'Change password'}
                </Button>
              </form>
            </CardContent>
          </Card>
          <Card className="ring-destructive/30">
            <CardHeader>
              <CardTitle>Delete account</CardTitle>
              <CardDescription>Your profile will be deactivated. You will be signed out immediately.</CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={deleteForm.handleSubmit(onDelete)}>
                <FormError message={formErrorMessage(removeAccount.error, deleteForm.formState.errors)} />
                <Field
                  id="profile-delete-password"
                  label="Password"
                  type="password"
                  autoComplete="current-password"
                  placeholder="••••••••"
                  error={deleteForm.formState.errors.password?.message}
                  {...deleteForm.register('password')}
                />
                <Button type="submit" variant="destructive" className="h-11 rounded-sm" disabled={removeAccount.isPending}>
                  <Trash2 />
                  {removeAccount.isPending ? 'Deleting…' : 'Delete account'}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>
      </div>
    </Container>
  )
}
