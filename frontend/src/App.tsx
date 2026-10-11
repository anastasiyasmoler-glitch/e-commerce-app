import { useState } from 'react'
import { BrowserRouter, Route, Routes } from 'react-router-dom'
import { getDisplayName } from './auth/session'
import { AuthModal, type AuthMode } from './components/auth/AuthModal'
import { StoreFooter } from './components/layout/StoreFooter'
import { StoreHeader } from './components/layout/StoreHeader'
import { HomePage } from './pages/HomePage'
import { ProfilePage } from './pages/ProfilePage'

export default function App() {
  const [name, setName] = useState<string | null>(getDisplayName())
  const [authMode, setAuthMode] = useState<AuthMode | null>(null)

  return (
    <BrowserRouter>
      <div className="flex min-h-screen flex-col bg-bg">
        <StoreHeader
          signedInName={name}
          onSignIn={() => setAuthMode('login')}
          onRegister={() => setAuthMode('register')}
        />
        <main className="flex-1">
          <Routes>
            <Route index element={<HomePage />} />
            <Route path="/profile" element={<ProfilePage onSignedOut={() => setName(null)} />} />
          </Routes>
        </main>
        <StoreFooter />
      </div>
      {authMode ? (
        <AuthModal
          key={authMode}
          initialMode={authMode}
          onClose={() => setAuthMode(null)}
          onSignedIn={() => setName(getDisplayName())}
        />
      ) : null}
    </BrowserRouter>
  )
}
