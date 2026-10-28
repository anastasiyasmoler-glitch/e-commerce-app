import { getAccessToken } from '@/api/http'

const gatewayUrl = (import.meta.env.VITE_AUTH_URL as string | undefined) ?? 'https://localhost'

const catalogAdminPath = '/catalog-admin'

/** Gateway URL for Catalog Filament (includes access JWT query param when logged in). */
export function buildCatalogAdminUrl(): string {
  const url = new URL(`${gatewayUrl}${catalogAdminPath}`)
  const token = getAccessToken()
  if (token) {
    url.searchParams.set('token', token)
  }

  return url.toString()
}

/**
 * Open Catalog Filament on the gateway. Token is only needed on the first request;
 * Catalog TrustGatewayAdmin logs you in and redirects to the same path without ?token=.
 */
export function openCatalogAdmin(): void {
  window.location.assign(buildCatalogAdminUrl())
}
