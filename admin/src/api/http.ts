import axios, { isAxiosError } from 'axios'

let accessToken: string | null = null

export function getAccessToken(): string | null {
  return accessToken
}

export function setAccessToken(token: string | null): void {
  accessToken = token
}

export const http = axios.create({
  baseURL: (import.meta.env.VITE_AUTH_URL as string | undefined) ?? 'https://localhost',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

http.interceptors.request.use((config) => {
  if (accessToken) {
    config.headers.Authorization = `Bearer ${accessToken}`
  }
  return config
})

export function apiErrorMessage(error: unknown): string {
  if (isAxiosError(error)) {
    const body = error.response?.data as
      | { message?: string; errors?: Record<string, string[]> }
      | undefined
    const first = body?.errors ? Object.values(body.errors).flat()[0] : undefined
    return first ?? body?.message ?? 'Request failed.'
  }
  return error instanceof Error ? error.message : 'Request failed.'
}
