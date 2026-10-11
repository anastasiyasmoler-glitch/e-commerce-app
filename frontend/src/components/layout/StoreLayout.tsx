import { useEffect, useState } from 'react'
import { Outlet, useLocation, useNavigate } from 'react-router-dom'
import { AuthModal, type AuthMode } from '../auth/AuthModal'
import { StoreFooter } from './StoreFooter'
import { StoreHeader } from './StoreHeader'

type LocationState = {
  auth?: AuthMode
  from?: string
}

export function StoreLayout() {
  const location = useLocation()
  const navigate = useNavigate()
  const state = location.state as LocationState | null
  const [authMode, setAuthMode] = useState<AuthMode | null>(null)

  useEffect(() => {
    if (!state?.auth) {
      return
    }

    setAuthMode(state.auth)
    navigate(location.pathname, { replace: true, state: { from: state.from } })
  }, [location.pathname, navigate, state?.auth, state?.from])

  return (
    <>
      <div className="flex min-h-screen flex-col bg-bg">
        <StoreHeader onSignIn={() => setAuthMode('login')} onRegister={() => setAuthMode('register')} />
        <main className="flex-1">
          <Outlet />
        </main>
        <StoreFooter />
      </div>
      {authMode ? (
        <AuthModal key={authMode} initialMode={authMode} onClose={() => setAuthMode(null)} />
      ) : null}
    </>
  )
}
