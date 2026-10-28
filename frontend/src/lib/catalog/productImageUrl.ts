const gatewayBase =
  (import.meta.env.VITE_AUTH_URL as string | undefined)?.replace(/\/$/, '') ?? 'https://localhost'

/** Build a browser-loadable URL from Catalog/Search image path (filename or full URL). */
export function productImagePublicUrl(imagePath?: string | null): string | undefined {
  if (!imagePath?.trim()) {
    return undefined
  }

  const trimmed = imagePath.trim()
  if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
    return trimmed
  }

  const path = trimmed.replace(/^\//, '')
  if (path.startsWith('product-images/')) {
    return `${gatewayBase}/${path}`
  }

  return `${gatewayBase}/product-images/${path}`
}
