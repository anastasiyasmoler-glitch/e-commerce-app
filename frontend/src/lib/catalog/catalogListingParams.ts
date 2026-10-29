export const CATALOG_DEFAULT_PER_PAGE = 9

export const CATALOG_PER_PAGE_OPTIONS = [9, 20, 50] as const

export type CatalogPerPage = (typeof CATALOG_PER_PAGE_OPTIONS)[number]

export function parseCatalogListingParams(searchParams: URLSearchParams): {
  page: number
  perPage: CatalogPerPage
} {
  const page = Math.max(1, Number.parseInt(searchParams.get('page') ?? '1', 10) || 1)
  const raw = Number.parseInt(searchParams.get('per_page') ?? String(CATALOG_DEFAULT_PER_PAGE), 10)
  const perPage = (CATALOG_PER_PAGE_OPTIONS as readonly number[]).includes(raw)
    ? (raw as CatalogPerPage)
    : CATALOG_DEFAULT_PER_PAGE

  return { page, perPage }
}

export function catalogListingQueryString(perPage: number, page = 1): string {
  const params = new URLSearchParams()
  if (page > 1) {
    params.set('page', String(page))
  }
  if (perPage !== CATALOG_DEFAULT_PER_PAGE) {
    params.set('per_page', String(perPage))
  }
  const query = params.toString()

  return query ? `?${query}` : ''
}
