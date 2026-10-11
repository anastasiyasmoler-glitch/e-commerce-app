import { useMemo, useState, type ReactNode } from 'react'
import { http } from '../lib/http'
import { isAdmin, setAccessToken, type AuthUser } from './session'
import { AuthContext } from './AuthContext'
import type { AuthContextValue } from './useAuth'

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null)

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      async login(email, password) {
        const loginRes = await http.post<{ access_token: string }>('/login', { email, password })
        setAccessToken(loginRes.data.access_token)
        const me = await http.get<AuthUser>('/me')
        if (!isAdmin(me.data)) {
          setAccessToken(null)
          throw new Error('Admin role required.')
        }
        setUser(me.data)
      },
      async logout() {
        try {
          await http.post('/logout')
        } finally {
          setAccessToken(null)
          setUser(null)
        }
      },
    }),
    [user],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
