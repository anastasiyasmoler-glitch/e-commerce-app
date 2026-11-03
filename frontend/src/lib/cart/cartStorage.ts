import type { CartLine } from '@/lib/cart/types'

const STORAGE_KEY = 'domo-storefront-cart-v1'

function isCartLine(value: unknown): value is CartLine {
  if (!value || typeof value !== 'object') {
    return false
  }

  const line = value as Record<string, unknown>

  return (
    typeof line.productId === 'number' &&
    Number.isFinite(line.productId) &&
    typeof line.storeId === 'number' &&
    Number.isFinite(line.storeId) &&
    typeof line.name === 'string' &&
    typeof line.slug === 'string' &&
    typeof line.priceCents === 'number' &&
    Number.isFinite(line.priceCents) &&
    typeof line.quantity === 'number' &&
    Number.isInteger(line.quantity) &&
    line.quantity > 0 &&
    (line.imageUrl === undefined ||
      line.imageUrl === null ||
      typeof line.imageUrl === 'string')
  )
}

export function readCartLinesFromStorage(): CartLine[] {
  if (typeof window === 'undefined') {
    return []
  }

  try {
    const raw = window.localStorage.getItem(STORAGE_KEY)
    if (!raw) {
      return []
    }

    const parsed: unknown = JSON.parse(raw)
    if (!Array.isArray(parsed)) {
      return []
    }

    return parsed.filter(isCartLine)
  } catch {
    return []
  }
}

export function writeCartLinesToStorage(lines: CartLine[]): void {
  if (typeof window === 'undefined') {
    return
  }

  try {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(lines))
  } catch {
    // Quota or private mode — ignore; in-memory cart still works this session.
  }
}
