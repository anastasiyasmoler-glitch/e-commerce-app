import { useState } from 'react'
import { Outlet } from 'react-router-dom'
import { AuthModal, type AuthMode } from '../auth/AuthModal'
import { StoreFooter } from './StoreFooter'
import { StoreHeader } from './StoreHeader'

export function StoreLayout() {
  const [authMode, setAuthMode] = useState<AuthMode | null>(null)

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
