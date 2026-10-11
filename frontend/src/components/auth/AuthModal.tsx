import { X } from 'lucide-react'
import { useState, type FormEvent } from 'react'
import { googleStartUrl, login, register } from '../../auth/session'

export type AuthMode = 'login' | 'register'

type AuthModalProps = {
  initialMode?: AuthMode
  onClose: () => void
  onSignedIn: () => void
}

const fieldClass =
  'h-11 w-full rounded-sm border border-line-strong bg-surface px-4 text-[15px] outline-none placeholder:text-muted/70 focus:border-brand focus:shadow-[0_0_0_3px_var(--brand-glow)]'

export function AuthModal({ initialMode = 'login', onClose, onSignedIn }: AuthModalProps) {
  const [mode, setMode] = useState<AuthMode>(initialMode)
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [phone, setPhone] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState<string | null>(null)
  const [pending, setPending] = useState(false)

  async function onSubmit(event: FormEvent) {
    event.preventDefault()
    setError(null)
    setPending(true)
    try {
      if (mode === 'login') {
        await login(email, password)
      } else {
        await register({
          name,
          email,
          phone,
          password,
          password_confirmation: password,
        })
      }
      onSignedIn()
      onClose()
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Could not continue.')
    } finally {
      setPending(false)
    }
  }

  return (
    <div
      className="fixed inset-0 z-[60] flex items-start justify-center bg-overlay px-4 pt-[10vh]"
      onClick={onClose}
      role="presentation"
    >
      <div
        className="relative w-full max-w-[420px] rounded-lg bg-surface p-8 shadow-[0_14px_40px_rgba(28,31,29,0.12)]"
        role="dialog"
        aria-modal="true"
        aria-labelledby="auth-title"
        onClick={(event) => event.stopPropagation()}
      >
        <button
          type="button"
          className="absolute top-4 right-4 text-muted hover:text-ink"
          onClick={onClose}
          aria-label="Close"
        >
          <X size={18} />
        </button>
        <h2 id="auth-title" className="font-display text-[28px] font-medium tracking-tight">
          {mode === 'login' ? 'Sign in' : 'Create account'}
        </h2>
        <p className="mt-1 mb-6 text-[14px] text-muted">
          {mode === 'login' ? 'Welcome back.' : 'Register to place orders and use the AI assistant.'}
        </p>
        {error ? <p className="mb-4 text-[13px] text-danger">{error}</p> : null}
        <form onSubmit={onSubmit}>
          {mode === 'register' ? (
            <>
              <div className="mb-4">
                <label className="mb-1.5 block text-[13px] font-semibold" htmlFor="auth-name">
                  Full name
                </label>
                <input
                  id="auth-name"
                  className={fieldClass}
                  placeholder="Anna Smith"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  required
                  autoComplete="name"
                />
              </div>
              <div className="mb-4">
                <label className="mb-1.5 block text-[13px] font-semibold" htmlFor="auth-phone">
                  Phone number
                </label>
                <input
                  id="auth-phone"
                  className={fieldClass}
                  type="tel"
                  placeholder="+1 555 123 4567"
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  required
                  autoComplete="tel"
                />
              </div>
            </>
          ) : null}
          <div className="mb-4">
            <label className="mb-1.5 block text-[13px] font-semibold" htmlFor="auth-email">
              Email
            </label>
            <input
              id="auth-email"
              className={fieldClass}
              type="email"
              placeholder="you@example.com"
              autoComplete="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </div>
          <div className="mb-6">
            <label className="mb-1.5 block text-[13px] font-semibold" htmlFor="auth-password">
              Password
            </label>
            <input
              id="auth-password"
              className={fieldClass}
              type="password"
              placeholder="••••••••"
              autoComplete={mode === 'login' ? 'current-password' : 'new-password'}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>
          <button
            type="submit"
            className="flex h-[52px] w-full items-center justify-center rounded-sm bg-brand text-[16px] font-semibold text-white hover:bg-brand-dark disabled:cursor-not-allowed disabled:bg-subtle disabled:text-muted"
            disabled={pending}
          >
            {pending ? 'Please wait…' : mode === 'login' ? 'Sign in' : 'Create account'}
          </button>
        </form>
        <a
          className="mt-3 flex h-11 w-full items-center justify-center rounded-sm border border-line bg-surface text-[14px] font-semibold hover:border-ink"
          href={googleStartUrl()}
        >
          Continue with Google
        </a>
        <p className="mt-5 text-center text-[13px] text-muted">
          {mode === 'login' ? (
            <>
              New to Domo?{' '}
              <button type="button" className="font-semibold text-brand" onClick={() => setMode('register')}>
                Create an account
              </button>
            </>
          ) : (
            <>
              Already have an account?{' '}
              <button type="button" className="font-semibold text-brand" onClick={() => setMode('login')}>
                Sign in
              </button>
            </>
          )}
        </p>
      </div>
    </div>
  )
}
