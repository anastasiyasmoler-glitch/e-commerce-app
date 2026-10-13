import { zodResolver } from '@hookform/resolvers/zod'
import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Navigate, useNavigate } from 'react-router-dom'
import { isStaff } from '@/api/authApi'
import { apiErrorMessage } from '@/api/http'
import { useLogin } from '@/hooks/useLogin'
import { useLogout } from '@/hooks/useLogout'
import { useMe } from '@/hooks/useMe'
import { useSession } from '@/hooks/useSession'
import { loginSchema, type LoginValues } from '@/lib/validators/auth'
import { Notice } from '@/components/ui/notice'
import { paths } from '@/paths'

export function LoginPage() {
  const session = useSession()
  const me = useMe()
  const login = useLogin()
  const logout = useLogout()
  const navigate = useNavigate()
  const [error, setError] = useState<string | null>(null)
  const {
    register,
    handleSubmit,
    formState: { isSubmitting },
  } = useForm<LoginValues>({ resolver: zodResolver(loginSchema) })

  if (session.data && me.data && isStaff(me.data)) {
    return <Navigate to={paths.analytics} replace />
  }

  async function onSubmit(values: LoginValues) {
    setError(null)
    try {
      const profile = await login.mutateAsync(values)
      if (!isStaff(profile)) {
        await logout.mutateAsync()
        setError('Admin or Analyst role required.')
        return
      }
      navigate(paths.analytics, { replace: true })
    } catch (err) {
      setError(apiErrorMessage(err))
    }
  }

  return (
    <div className="auth">
      <div className="auth-side">
        <a href={paths.analytics} className="logo">
          Domo<i>.</i>
        </a>
        <blockquote>Admin panel for the Domo marketplace.</blockquote>
      </div>
      <div className="auth-main">
        <form className="auth-form" onSubmit={handleSubmit(onSubmit)}>
          <h1>Admin sign in</h1>
          <p className="sub">Use your staff account to continue.</p>
          {error ? <Notice tone="danger">{error}</Notice> : null}
          <div className="field">
            <label htmlFor="email">Email</label>
            <input
              id="email"
              className="input"
              type="email"
              placeholder="you@example.com"
              autoComplete="username"
              {...register('email')}
            />
          </div>
          <div className="field">
            <label htmlFor="password">Password</label>
            <input
              id="password"
              className="input"
              type="password"
              placeholder="••••••••"
              autoComplete="current-password"
              {...register('password')}
            />
          </div>
          <button type="submit" className="btn btn-primary btn-block" disabled={isSubmitting}>
            {isSubmitting ? 'Signing in…' : 'Sign in'}
          </button>
        </form>
      </div>
    </div>
  )
}
