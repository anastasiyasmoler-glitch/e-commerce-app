import { LogOut, Package, Sparkles, Trash2, User } from 'lucide-react'
import { Controller } from 'react-hook-form'
import { Link } from 'react-router-dom'
import { firstFormError } from '@/lib/form'
import { useProfilePage } from '@/hooks/useProfilePage'
import { paths } from '@/paths'
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
import { FieldSkeleton, Skeleton } from '@/components/ui/skeleton'
import { Separator } from '@/components/ui/separator'

export function ProfilePage() {
  const profile = useProfilePage()

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
        <Button
          type="button"
          variant="outline"
          className="h-11 rounded-sm"
          onClick={profile.onSignOut}
          disabled={profile.logout.isPending}
        >
          <LogOut />
          {profile.logout.isPending ? 'Signing out…' : 'Sign out'}
        </Button>
      </div>
      <div className="grid gap-10 lg:grid-cols-[240px_1fr]">
        <nav className="flex flex-col gap-1">
          <Button variant="secondary" className="justify-start" type="button">
            <User /> Profile
          </Button>
          <Button variant="ghost" className="justify-start" type="button" asChild>
            <Link to={paths.orders}>
              <Package /> My orders
            </Link>
          </Button>
          <Button variant="ghost" className="justify-start" type="button" asChild>
            <Link to={paths.assistant}>
              <Sparkles /> AI Assistant
            </Link>
          </Button>
          <Separator className="my-2" />
          <Button
            type="button"
            variant="ghost"
            className="justify-start"
            onClick={profile.onSignOut}
            disabled={profile.logout.isPending}
          >
            <LogOut /> Sign out
          </Button>
        </nav>
        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Personal details</CardTitle>
              <CardDescription className="flex items-center gap-2">
                Role:{' '}
                {profile.detailsLoading ? (
                  <Skeleton className="inline-block h-5 w-20 rounded-4xl" />
                ) : (
                  <Badge variant="secondary">{profile.role}</Badge>
                )}
              </CardDescription>
            </CardHeader>
            <CardContent>
              {profile.detailsLoading ? (
                <>
                  <div className="mt-4 grid gap-4 sm:grid-cols-2">
                    <FieldSkeleton label="Full name" />
                    <FieldSkeleton label="Phone" />
                  </div>
                  <FieldSkeleton label="Email" />
                  <Skeleton className="h-11 w-36" />
                </>
              ) : (
                <form onSubmit={profile.detailsForm.handleSubmit(profile.onSaveDetails)}>
                  <FormError message={firstFormError(profile.detailsForm.formState.errors)} />
                  <div className="mt-4 grid gap-4 sm:grid-cols-2">
                    <Field
                      id="profile-name"
                      label="Full name"
                      autoComplete="name"
                      error={profile.detailsForm.formState.errors.name?.message}
                      {...profile.detailsForm.register('name')}
                    />
                    <Controller
                      control={profile.detailsForm.control}
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
                    error={profile.detailsForm.formState.errors.email?.message}
                    {...profile.detailsForm.register('email')}
                  />
                  <Button
                    type="submit"
                    className="h-11 rounded-sm"
                    disabled={profile.updateProfile.isPending}
                  >
                    {profile.updateProfile.isPending ? 'Saving…' : 'Save changes'}
                  </Button>
                </form>
              )}
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>Password</CardTitle>
              <CardDescription>Use at least 8 characters.</CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={profile.passwordForm.handleSubmit(profile.onChangePassword)}>
                <FormError message={firstFormError(profile.passwordForm.formState.errors)} />
                <Field
                  id="profile-current-password"
                  label="Current password"
                  type="password"
                  autoComplete="current-password"
                  placeholder="••••••••"
                  error={profile.passwordForm.formState.errors.current_password?.message}
                  {...profile.passwordForm.register('current_password')}
                />
                <Field
                  id="profile-new-password"
                  label="New password"
                  type="password"
                  autoComplete="new-password"
                  placeholder="••••••••"
                  error={profile.passwordForm.formState.errors.password?.message}
                  {...profile.passwordForm.register('password')}
                />
                <Field
                  id="profile-confirm-password"
                  label="Confirm new password"
                  type="password"
                  autoComplete="new-password"
                  placeholder="••••••••"
                  error={profile.passwordForm.formState.errors.password_confirmation?.message}
                  {...profile.passwordForm.register('password_confirmation')}
                />
                <Button
                  type="submit"
                  variant="outline"
                  className="h-11 rounded-sm"
                  disabled={profile.changePassword.isPending}
                >
                  {profile.changePassword.isPending ? 'Updating…' : 'Change password'}
                </Button>
              </form>
            </CardContent>
          </Card>
          <Card className="ring-destructive/30">
            <CardHeader>
              <CardTitle>Delete account</CardTitle>
              <CardDescription>
                Your profile will be deactivated. You will be signed out immediately.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={profile.deleteForm.handleSubmit(profile.onDelete)}>
                <FormError message={firstFormError(profile.deleteForm.formState.errors)} />
                <Field
                  id="profile-delete-password"
                  label="Password"
                  type="password"
                  autoComplete="current-password"
                  placeholder="••••••••"
                  error={profile.deleteForm.formState.errors.password?.message}
                  {...profile.deleteForm.register('password')}
                />
                <Button
                  type="submit"
                  variant="destructive"
                  className="h-11 rounded-sm"
                  disabled={profile.removeAccount.isPending}
                >
                  <Trash2 />
                  {profile.removeAccount.isPending ? 'Deleting…' : 'Delete account'}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>
      </div>
    </Container>
  )
}
