import { Navigate } from 'react-router-dom'
import { useLoginPage } from '@/hooks/useLoginPage'
import { paths } from '@/paths'
import { Notice } from '@/components/ui/notice'

export function LoginPage() {
  const loginPage = useLoginPage()
  const { register, handleSubmit } = loginPage.form

  if (loginPage.redirectToApp) {
    return <Navigate to={paths.analytics} replace />
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
        <form className="auth-form" onSubmit={handleSubmit(loginPage.onSubmit)}>
          <h1>Admin sign in</h1>
          <p className="sub">Use your staff account to continue.</p>
          {loginPage.error ? <Notice tone="danger">{loginPage.error}</Notice> : null}
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
          <button type="submit" className="btn btn-primary btn-block" disabled={loginPage.isSubmitting}>
            {loginPage.isSubmitting ? 'Signing in…' : 'Sign in'}
          </button>
        </form>
      </div>
    </div>
  )
}
