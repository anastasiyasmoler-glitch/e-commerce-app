import type { SearchParams } from '@/api/searchApi'

export function parseSearchRequest(
  searchParams: URLSearchParams,
  storeId: number,
): SearchParams {
  const q = searchParams.get('q') ?? ''
  const categoryIds = searchParams.getAll('category_id')
  const page = Number(searchParams.get('page') ?? '1')
  const sort = searchParams.get('sort') as SearchParams['sort'] | null
  const availableRaw = searchParams.get('available')

  let available: boolean | undefined
  if (availableRaw === 'true') {
    available = true
  } else if (availableRaw === 'false') {
    available = false
  }

  return {
    store_id: storeId,
    q,
    category_id: categoryIds.length > 0 ? categoryIds : undefined,
    page: Number.isFinite(page) && page > 0 ? page : 1,
    per_page: 20,
    sort: sort === 'price_asc' || sort === 'price_desc' ? sort : 'relevance',
    available,
  }
}

export function toggleCategoryId(current: string[], id: string): string[] {
  if (current.includes(id)) {
    return current.filter((value) => value !== id)
  }
  return [...current, id]
}
