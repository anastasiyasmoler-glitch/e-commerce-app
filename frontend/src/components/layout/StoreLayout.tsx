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
  const authMode = state?.auth ?? null

  const openAuth = (mode: AuthMode, from?: string) => {
    navigate(location.pathname, { replace: true, state: { auth: mode, from } })
  }

  const closeAuth = () => {
    navigate(location.pathname, { replace: true, state: undefined })
  }

  return (
    <>
      <div className="flex min-h-screen flex-col bg-bg has-[[data-slot=page-loading]]:[&_footer]:hidden has-[[data-slot=chat-page]]:[&_footer]:hidden">
        <StoreHeader onSignIn={() => openAuth('login')} onRegister={() => openAuth('register')} />
        <main className="flex min-h-0 flex-1 flex-col">
          <Outlet />
        </main>
        <StoreFooter />
      </div>
      {authMode ? <AuthModal key={authMode} initialMode={authMode} onClose={closeAuth} /> : null}
    </>
  )
}
