import { useState } from 'react'
import { BrowserRouter, Route, Routes } from 'react-router-dom'
import { getDisplayName } from './auth/session'
import { AuthModal } from './components/auth/AuthModal'
import { StoreHeader } from './components/layout/StoreHeader'
import { HomePage } from './pages/HomePage'

export default function App() {
  const [name, setName] = useState<string | null>(getDisplayName())
  const [authOpen, setAuthOpen] = useState(false)

  return (
    <BrowserRouter>
      <StoreHeader signedInName={name} onSignIn={() => setAuthOpen(true)} />
      <main>
        <Routes>
          <Route index element={<HomePage />} />
        </Routes>
      </main>
      {authOpen ? (
        <AuthModal
          onClose={() => setAuthOpen(false)}
          onSignedIn={() => setName(getDisplayName())}
        />
      ) : null}
    </BrowserRouter>
  )
}
