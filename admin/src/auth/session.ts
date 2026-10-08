export const authUrl = () =>
  (import.meta.env.VITE_AUTH_URL as string | undefined) ?? 'https://localhost'

let accessToken: string | null = null

export function getAccessToken(): string | null {
  return accessToken
}

export function setAccessToken(token: string | null): void {
  accessToken = token
}

export type AuthUser = {
  id: number
  name: string
  email: string
  roles: string[]
}

export function isAdmin(user: AuthUser | null): boolean {
  return Boolean(user?.roles.includes('admin'))
}
