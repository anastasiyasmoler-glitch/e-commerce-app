import { http } from './http'

export type SearchFacetValue = {
  value: string
  label?: string
  count: number
}

export type SearchFacets = {
  category_id?: SearchFacetValue[]
  available?: SearchFacetValue[]
  price?: SearchFacetValue[]
  attributes?: Record<string, SearchFacetValue[]>
}

export type SearchItem = {
  id: string
  store_id: string
  category_id: string
  name: string
  slug: string
  sku?: string
  description?: string
  price: number
  attributes?: Record<string, string>
  image?: string
  available: boolean
}

export type SearchResult = {
  query: string
  items: SearchItem[]
  facets: SearchFacets
  pagination: {
    page: number
    per_page: number
    total: number
    total_pages: number
  }
}

export type SearchParams = {
  q?: string
  store_id: number
  category_id?: string[]
  available?: boolean
  price_min?: number
  price_max?: number
  page?: number
  per_page?: number
  sort?: 'relevance' | 'price_asc' | 'price_desc'
  attributes?: Record<string, string>
}

export const searchKeys = {
  all: ['search'] as const,
  query: (storeId: number | undefined, params: SearchParams) =>
    ['search', storeId, params] as const,
}

export async function fetchSearch(params: SearchParams): Promise<SearchResult> {
  const query = new URLSearchParams()
  query.set('store_id', String(params.store_id))
  if (params.q?.trim()) {
    query.set('q', params.q.trim())
  }
  if (params.page) {
    query.set('page', String(params.page))
  }
  if (params.per_page) {
    query.set('per_page', String(params.per_page))
  }
  if (params.sort) {
    query.set('sort', params.sort)
  }
  if (params.available !== undefined) {
    query.set('available', params.available ? 'true' : 'false')
  }
  if (params.price_min !== undefined) {
    query.set('price_min', String(params.price_min))
  }
  if (params.price_max !== undefined) {
    query.set('price_max', String(params.price_max))
  }
  for (const id of params.category_id ?? []) {
    query.append('category_id', id)
  }
  for (const [code, value] of Object.entries(params.attributes ?? {})) {
    query.set(`attr.${code}`, value)
  }

  const { data } = await http.get<{ data: SearchResult }>(`/api/search?${query.toString()}`)
  return data.data
}
