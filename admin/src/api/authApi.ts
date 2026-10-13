import { isAxiosError } from 'axios'
import { getAccessToken, http, setAccessToken } from './http'
import { queryClient } from './queryClient'

export type TokenResponse = {
  access_token: string
  token_type: string
  expires_in: number
}

export type MeResponse = {
  id: number
  name: string
  phone: string | null
  email: string
  roles: string[]
}

export const authKeys = {
  all: ['auth'] as const,
  session: ['auth', 'session'] as const,
  me: ['auth', 'me'] as const,
}

export function isStaff(user: MeResponse | null | undefined): boolean {
  return Boolean(user?.roles.includes('admin') || user?.roles.includes('analyst'))
}

let sessionRestored: boolean | null = null

function rememberAccess(tokens: TokenResponse): TokenResponse {
  setAccessToken(tokens.access_token)
  sessionRestored = true
  return tokens
}

export function resetSessionRestore(): void {
  sessionRestored = null
}

export async function restoreSession(): Promise<boolean> {
  if (sessionRestored !== null) {
    return sessionRestored
  }

  try {
    await refreshOnce()
    sessionRestored = true
  } catch {
    setAccessToken(null)
    sessionRestored = false
  }

  return sessionRestored
}

export async function login(values: { email: string; password: string }): Promise<TokenResponse> {
  const { data } = await http.post<TokenResponse>('/login', values)
  return rememberAccess(data)
}

export async function refresh(): Promise<TokenResponse> {
  const { data } = await http.post<TokenResponse>('/refresh')
  return rememberAccess(data)
}

let refreshPromise: Promise<TokenResponse> | null = null

export function refreshOnce(): Promise<TokenResponse> {
  refreshPromise ??= refresh().finally(() => {
    refreshPromise = null
  })
  return refreshPromise
}

http.interceptors.response.use(
  (response) => response,
  async (error: unknown) => {
    if (!isAxiosError(error) || !error.config) {
      return Promise.reject(error)
    }

    const original = error.config as typeof error.config & { _retried?: boolean }
    const url: string = original.url ?? ''

    if (
      error.response?.status !== 401 ||
      original._retried ||
      url === '/refresh' ||
      url === '/login' ||
      url === '/logout'
    ) {
      return Promise.reject(error)
    }

    original._retried = true

    try {
      await refreshOnce()
    } catch {
      setAccessToken(null)
      resetSessionRestore()
      queryClient.setQueryData(authKeys.session, false)
      queryClient.removeQueries({ queryKey: authKeys.me })
      return Promise.reject(error)
    }

    const token = getAccessToken()
    if (token) {
      original.headers.Authorization = `Bearer ${token}`
    }

    return http(original)
  },
)

export async function logout(): Promise<void> {
  await http.post('/logout')
  setAccessToken(null)
  resetSessionRestore()
}

export async function me(): Promise<MeResponse> {
  const { data } = await http.get<MeResponse>('/me')
  return data
}
