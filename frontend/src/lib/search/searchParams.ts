import type { SearchParams } from '@/api/searchApi'

const SORT_VALUES = ['relevance', 'price_asc', 'price_desc'] as const

export function parseAttributeFilters(searchParams: URLSearchParams): Record<string, string> {
  const attributes: Record<string, string> = {}

  searchParams.forEach((value, key) => {
    if (key.startsWith('attr.')) {
      attributes[key.slice(5)] = value
    }
  })

  return attributes
}

export function parseSearchRequest(
  searchParams: URLSearchParams,
  storeId: number,
): SearchParams {
  const q = searchParams.get('q') ?? ''
  const categoryIds = searchParams.getAll('category_id')
  const page = Number(searchParams.get('page') ?? '1')
  const sortRaw = searchParams.get('sort')
  const availableRaw = searchParams.get('available')
  const priceMinRaw = searchParams.get('price_min')
  const priceMaxRaw = searchParams.get('price_max')

  let available: boolean | undefined
  if (availableRaw === 'true') {
    available = true
  } else if (availableRaw === 'false') {
    available = false
  }

  const priceMin = priceMinRaw !== null ? Number(priceMinRaw) : undefined
  const priceMax = priceMaxRaw !== null ? Number(priceMaxRaw) : undefined

  const sort =
    sortRaw && (SORT_VALUES as readonly string[]).includes(sortRaw)
      ? (sortRaw as SearchParams['sort'])
      : 'relevance'

  return {
    store_id: storeId,
    q,
    category_id: categoryIds.length > 0 ? categoryIds : undefined,
    page: Number.isFinite(page) && page > 0 ? page : 1,
    per_page: 20,
    sort,
    available,
    price_min: priceMin !== undefined && Number.isFinite(priceMin) && priceMin >= 0 ? priceMin : undefined,
    price_max: priceMax !== undefined && Number.isFinite(priceMax) && priceMax >= 0 ? priceMax : undefined,
    attributes: (() => {
      const attrs = parseAttributeFilters(searchParams)
      return Object.keys(attrs).length > 0 ? attrs : undefined
    })(),
  }
}

export function toggleCategoryId(current: string[], id: string): string[] {
  if (current.includes(id)) {
    return current.filter((value) => value !== id)
  }
  return [...current, id]
}

export function priceRangeFromFacetKey(key: string): { price_min?: number; price_max?: number } {
  if (key === '50000+') {
    return { price_min: 50000 }
  }

  const parts = key.split('-')
  if (parts.length !== 2) {
    return {}
  }

  const min = Number(parts[0])
  const max = Number(parts[1])

  return {
    price_min: Number.isFinite(min) ? min : undefined,
    price_max: Number.isFinite(max) ? max : undefined,
  }
}

export function formatPriceCents(cents: number): string {
  return `$${(cents / 100).toLocaleString(undefined, {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  })}`
}
