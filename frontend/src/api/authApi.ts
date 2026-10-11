import { http, setAccessToken } from './http'
import type { LoginValues, RegisterValues } from '@/lib/validators/auth'

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

function rememberAccess(tokens: TokenResponse): TokenResponse {
  setAccessToken(tokens.access_token)
  return tokens
}

export function googleStartUrl(): string {
  return `${http.defaults.baseURL}/auth/google`
}

export async function login(values: LoginValues): Promise<TokenResponse> {
  const { data } = await http.post<TokenResponse>('/login', values)
  return rememberAccess(data)
}

export async function register(values: RegisterValues): Promise<TokenResponse> {
  const { data } = await http.post<TokenResponse>('/register', {
    ...values,
    password_confirmation: values.password,
  })
  return rememberAccess(data)
}

export async function refresh(): Promise<TokenResponse> {
  const { data } = await http.post<TokenResponse>('/refresh')
  return rememberAccess(data)
}

export async function logout(): Promise<void> {
  await http.post('/logout')
  setAccessToken(null)
}

export async function me(): Promise<MeResponse> {
  const { data } = await http.get<MeResponse>('/me')
  return data
}
