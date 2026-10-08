import { X } from 'lucide-react'
import { FormEvent, useState } from 'react'
import { googleStartUrl, login, register } from '../../auth/session'

type Mode = 'login' | 'register'

type AuthModalProps = {
  onClose: () => void
  onSignedIn: () => void
}

export function AuthModal({ onClose, onSignedIn }: AuthModalProps) {
  const [mode, setMode] = useState<Mode>('login')
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [phone, setPhone] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
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
          password_confirmation: passwordConfirmation,
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
      className="modal-overlay"
      onClick={onClose}
      role="presentation"
    >
      <div
        className="auth-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="auth-title"
        onClick={(event) => event.stopPropagation()}
      >
        <button type="button" className="modal-close" onClick={onClose} aria-label="Close">
          <X size={18} />
        </button>
        <div className="auth-tabs" role="tablist">
          <button
            type="button"
            role="tab"
            aria-selected={mode === 'login'}
            className={mode === 'login' ? 'auth-tab active' : 'auth-tab'}
            onClick={() => setMode('login')}
          >
            Sign in
          </button>
          <button
            type="button"
            role="tab"
            aria-selected={mode === 'register'}
            className={mode === 'register' ? 'auth-tab active' : 'auth-tab'}
            onClick={() => setMode('register')}
          >
            Register
          </button>
        </div>
        <h1 id="auth-title">{mode === 'login' ? 'Sign in' : 'Create account'}</h1>
        {error ? <p className="auth-error">{error}</p> : null}
        <form onSubmit={onSubmit}>
          {mode === 'register' ? (
            <>
              <div className="field">
                <label htmlFor="auth-name">Name</label>
                <input
                  id="auth-name"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  required
                  autoComplete="name"
                />
              </div>
              <div className="field">
                <label htmlFor="auth-phone">Phone</label>
                <input
                  id="auth-phone"
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  required
                  autoComplete="tel"
                />
              </div>
            </>
          ) : null}
          <div className="field">
            <label htmlFor="auth-email">Email</label>
            <input
              id="auth-email"
              type="email"
              autoComplete="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </div>
          <div className="field">
            <label htmlFor="auth-password">Password</label>
            <input
              id="auth-password"
              type="password"
              autoComplete={mode === 'login' ? 'current-password' : 'new-password'}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>
          {mode === 'register' ? (
            <div className="field">
              <label htmlFor="auth-password-2">Confirm password</label>
              <input
                id="auth-password-2"
                type="password"
                autoComplete="new-password"
                value={passwordConfirmation}
                onChange={(e) => setPasswordConfirmation(e.target.value)}
                required
              />
            </div>
          ) : null}
          <button type="submit" className="btn btn-primary" disabled={pending}>
            {pending ? 'Please wait…' : mode === 'login' ? 'Sign in' : 'Register'}
          </button>
        </form>
        <div className="auth-divider">or</div>
        <a className="btn btn-ghost" href={googleStartUrl()}>
          Continue with Google
        </a>
      </div>
    </div>
  )
}
