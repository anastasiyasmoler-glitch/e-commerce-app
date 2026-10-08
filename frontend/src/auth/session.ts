let accessToken: string | null = null
let displayName: string | null = null

export const authUrl = () =>
  (import.meta.env.VITE_AUTH_URL as string | undefined) ?? 'https://localhost'

export function getAccessToken(): string | null {
  return accessToken
}

export function getDisplayName(): string | null {
  return displayName
}

export function clearSession(): void {
  accessToken = null
  displayName = null
}

export function googleStartUrl(): string {
  return `${authUrl()}/auth/google`
}

async function readError(response: Response): Promise<string> {
  const body = (await response.json().catch(() => ({}))) as {
    message?: string
    errors?: Record<string, string[]>
  }
  const first = body.errors ? Object.values(body.errors).flat()[0] : undefined
  return first ?? body.message ?? 'Request failed.'
}

async function applyTokens(access: string, fallbackName: string): Promise<void> {
  accessToken = access
  const me = await fetch(`${authUrl()}/me`, {
    headers: { Accept: 'application/json', Authorization: `Bearer ${accessToken}` },
    credentials: 'include',
  })
  if (me.ok) {
    const profile = (await me.json()) as { name?: string }
    displayName = profile.name ?? fallbackName
  } else {
    displayName = fallbackName
  }
}

export async function login(email: string, password: string): Promise<void> {
  const response = await fetch(`${authUrl()}/login`, {
    method: 'POST',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ email, password }),
  })
  if (!response.ok) {
    throw new Error(await readError(response))
  }
  const data = (await response.json()) as { access_token: string }
  await applyTokens(data.access_token, email)
}

export async function register(input: {
  name: string
  email: string
  phone: string
  password: string
  password_confirmation: string
}): Promise<void> {
  const response = await fetch(`${authUrl()}/register`, {
    method: 'POST',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify(input),
  })
  if (!response.ok) {
    throw new Error(await readError(response))
  }
  const data = (await response.json()) as { access_token: string }
  await applyTokens(data.access_token, input.name)
}
