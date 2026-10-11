import { LogOut, Package, Sparkles, Trash2, User } from 'lucide-react'
import { type FormEvent } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useLogout } from '@/hooks/useLogout'
import { useMe } from '@/hooks/useMe'
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
import { Separator } from '@/components/ui/separator'

export function ProfilePage() {
  const me = useMe().data
  const logout = useLogout()
  const navigate = useNavigate()

  function onSave(event: FormEvent) {
    event.preventDefault()
  }

  function onSignOut() {
    logout.mutate(undefined, {
      onSettled: () => {
        navigate(paths.home)
      },
    })
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
              <form onSubmit={onSave}>
                <div className="grid gap-4 sm:grid-cols-2">
                  <Field id="profile-name" label="Full name" defaultValue={me?.name ?? ''} key={me?.name} />
                  <Field
                    id="profile-phone"
                    label="Phone"
                    defaultValue={me?.phone ?? ''}
                    key={me?.phone ?? 'phone'}
                  />
                </div>
                <Field id="profile-email" label="Email" type="email" defaultValue={me?.email ?? ''} key={me?.email} />
                <Button type="submit" className="h-11 rounded-sm">
                  Save changes
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
              <form onSubmit={onSave}>
                <Field id="profile-current-password" label="Current password" type="password" placeholder="••••••••" />
                <Field id="profile-new-password" label="New password" type="password" placeholder="••••••••" />
                <Button type="submit" variant="outline" className="h-11 rounded-sm">
                  Change password
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
              <Button type="button" variant="destructive" className="h-11 rounded-sm">
                <Trash2 /> Delete account
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </Container>
  )
}
