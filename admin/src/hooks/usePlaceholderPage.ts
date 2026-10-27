import { getAccessToken } from '@/api/http'

const gatewayUrl = (import.meta.env.VITE_AUTH_URL as string | undefined) ?? 'https://localhost'

export function usePlaceholderPage(filamentPath = '/catalog-admin') {
  function openFilament() {
    const token = getAccessToken()
    const url = new URL(`${gatewayUrl}${filamentPath}`)
    if (token) {
      url.searchParams.set('token', token)
    }
    window.open(url.toString(), '_blank', 'noopener,noreferrer')
  }

  return { openFilament }
}
