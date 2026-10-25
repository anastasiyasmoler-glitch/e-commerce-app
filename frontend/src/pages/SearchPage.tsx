import { useMemo } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { useSelectedStore } from '@/catalog/useSelectedStore'
import { SearchProductCard } from '@/components/catalog/SearchProductCard'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { PageLoading } from '@/components/ui/loading-screen'
import { useSearch } from '@/hooks/useSearch'
import type { SearchParams } from '@/api/searchApi'
import { paths } from '@/paths'

function parseSearchParams(searchParams: URLSearchParams, storeId: number): SearchParams {
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

function toggleCategoryId(current: string[], id: string): string[] {
  if (current.includes(id)) {
    return current.filter((value) => value !== id)
  }
  return [...current, id]
}

export function SearchPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const { selectedStore } = useSelectedStore()
  const storeId = selectedStore?.id
  const storeName = selectedStore?.name ?? ''

  const request = useMemo(
    () => (storeId !== undefined ? parseSearchParams(searchParams, storeId) : null),
    [searchParams, storeId],
  )

  const searchQuery = useSearch(storeId, request ?? { store_id: 0, q: '' })
  const loading = storeId === undefined || (request !== null && searchQuery.isLoading && !searchQuery.data)
  const categories = searchQuery.data?.facets.category_id ?? []
  const activeCategoryIds = request?.category_id ?? []

  function updateParams(mutator: (params: URLSearchParams) => void) {
    const next = new URLSearchParams(searchParams)
    mutator(next)
    next.delete('page')
    setSearchParams(next)
  }

  function toggleCategory(categoryId: string) {
    updateParams((params) => {
      const current = params.getAll('category_id')
      const next = toggleCategoryId(current, categoryId)
      params.delete('category_id')
      for (const id of next) {
        params.append('category_id', id)
      }
    })
  }

  if (loading) {
    return <PageLoading label="Searching…" />
  }

  if (!request || !selectedStore) {
    return (
      <Container className="py-20 text-center">
        <h1 className="font-display text-[28px] font-medium">Select a store to search</h1>
        <Button asChild className="mt-6 rounded-[6px] bg-brand">
          <Link to={paths.home}>Back to home</Link>
        </Button>
      </Container>
    )
  }

  if (searchQuery.isError) {
    return (
      <Container className="py-20 text-center">
        <h1 className="font-display text-[28px] font-medium">Search is unavailable</h1>
        <p className="mt-2 text-[15px] text-muted">Check that Search and OpenSearch are running.</p>
      </Container>
    )
  }

  const result = searchQuery.data
  const items = result?.items ?? []
  const total = result?.pagination.total ?? 0
  const queryLabel = request.q?.trim() ? request.q.trim() : 'All products'

  return (
    <Container className="py-10">
      <h1 className="font-display text-[32px] font-medium tracking-[-0.02em]">
        Search: {queryLabel}
      </h1>
      <p className="mt-2 text-[15px] text-muted">
        {total} result{total === 1 ? '' : 's'} in {storeName}
      </p>

      <div className="mt-10 grid gap-10 lg:grid-cols-[240px_1fr]">
        <aside>
          <h2 className="text-[13px] font-semibold tracking-[0.08em] text-muted uppercase">
            Categories
          </h2>
          {categories.length === 0 ? (
            <p className="mt-3 text-[14px] text-muted">No category facets yet.</p>
          ) : (
            <ul className="mt-3 space-y-2">
              {categories.map((facet) => {
                const active = activeCategoryIds.includes(facet.value)
                const label = facet.label ?? facet.value
                return (
                  <li key={facet.value}>
                    <button
                      type="button"
                      onClick={() => toggleCategory(facet.value)}
                      className={`flex w-full items-center justify-between rounded-[6px] border px-3 py-2 text-left text-[14px] transition-colors ${
                        active
                          ? 'border-brand bg-brand-soft text-ink'
                          : 'border-line text-ink hover:border-brand'
                      }`}
                    >
                      <span>{label}</span>
                      <span className="text-muted">{facet.count}</span>
                    </button>
                  </li>
                )
              })}
            </ul>
          )}
          {activeCategoryIds.length > 0 ? (
            <Button
              type="button"
              variant="outline"
              className="mt-4 w-full rounded-[6px]"
              onClick={() =>
                updateParams((params) => {
                  params.delete('category_id')
                })
              }
            >
              Clear categories
            </Button>
          ) : null}
        </aside>

        <section>
          {items.length === 0 ? (
            <p className="text-[15px] text-muted">No products match your search.</p>
          ) : (
            <div className="grid grid-cols-2 gap-x-3 gap-y-8 sm:gap-x-5 lg:grid-cols-3 xl:grid-cols-4">
              {items.map((product) => (
                <SearchProductCard key={product.id} product={product} storeName={storeName} />
              ))}
            </div>
          )}

          {result && result.pagination.total_pages > 1 ? (
            <div className="mt-10 flex items-center justify-center gap-3">
              <Button
                type="button"
                variant="outline"
                className="rounded-[6px]"
                disabled={result.pagination.page <= 1}
                onClick={() => {
                  const next = new URLSearchParams(searchParams)
                  next.set('page', String(result.pagination.page - 1))
                  setSearchParams(next)
                }}
              >
                Previous
              </Button>
              <span className="text-[14px] text-muted">
                Page {result.pagination.page} of {result.pagination.total_pages}
              </span>
              <Button
                type="button"
                variant="outline"
                className="rounded-[6px]"
                disabled={result.pagination.page >= result.pagination.total_pages}
                onClick={() => {
                  const next = new URLSearchParams(searchParams)
                  next.set('page', String(result.pagination.page + 1))
                  setSearchParams(next)
                }}
              >
                Next
              </Button>
            </div>
          ) : null}
        </section>
      </div>
    </Container>
  )
}
