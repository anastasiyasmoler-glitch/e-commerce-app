import { useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { useSelectedStore } from '@/catalog/SelectedStoreContext'
import { ProductCard } from '@/components/catalog/ProductCard'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import { Container } from '@/components/ui/container'
import { PageLoading } from '@/components/ui/loading-screen'
import { useCategoryListing } from '@/hooks/useProducts'
import { categoryPath, paths } from '@/paths'

export function CategoryPage() {
  const { slug } = useParams()
  const { selectedStore } = useSelectedStore()
  const [page, setPage] = useState(1)
  const { roots, current, products, pageSize } = useCategoryListing(selectedStore?.id, slug, page)

  const title = current?.name ?? 'Catalog'
  const rows = products.data ?? []
  const storeName = selectedStore?.name ?? ''
  const loading = roots.isPending || products.isPending || products.isFetching

  const facetLinks = useMemo(
    () => [
      { slug: null as string | null, name: 'All' },
      ...(roots.data ?? []).map((category) => ({ slug: category.slug, name: category.name })),
    ],
    [roots.data],
  )

  if (loading) {
    return <PageLoading />
  }

  if (products.isError) {
    return (
      <Container className="py-20 text-center">
        <h2 className="font-display text-[28px] font-medium">Products could not be loaded</h2>
        <p className="mt-2 text-[15px] text-muted">Please try again later.</p>
      </Container>
    )
  }

  return (
    <Container className="pb-4">
      <Breadcrumb className="mt-6">
        <BreadcrumbList>
          <BreadcrumbItem>
            <BreadcrumbLink asChild>
              <Link to={paths.home}>Home</Link>
            </BreadcrumbLink>
          </BreadcrumbItem>
          <BreadcrumbSeparator />
          <BreadcrumbItem>
            {current ? (
              <BreadcrumbLink asChild>
                <Link to={paths.catalog}>Catalog</Link>
              </BreadcrumbLink>
            ) : (
              <BreadcrumbPage>Catalog</BreadcrumbPage>
            )}
          </BreadcrumbItem>
          {current ? (
            <>
              <BreadcrumbSeparator />
              <BreadcrumbItem>
                <BreadcrumbPage>{current.name}</BreadcrumbPage>
              </BreadcrumbItem>
            </>
          ) : null}
        </BreadcrumbList>
      </Breadcrumb>

      <div className="mt-4 grid grid-cols-1 items-start gap-12 lg:grid-cols-[260px_1fr]">
        <aside className="lg:sticky lg:top-[88px]">
          <div className="mb-2 flex items-baseline justify-between border-b border-ink pb-4">
            <h2 className="text-[16px] font-semibold">Filters</h2>
          </div>
          <div className="border-b border-line py-5">
            <h3 className="mb-3 text-[13px] font-semibold tracking-[0.08em] uppercase">Category</h3>
            <div className="flex flex-col">
              {facetLinks.map((item) => {
                const isActive = (item.slug ?? undefined) === slug
                return (
                  <Link
                    key={item.slug ?? 'all'}
                    to={item.slug ? categoryPath(item.slug) : paths.catalog}
                    onClick={() => setPage(1)}
                    className={`py-[5px] text-[15px] transition-colors ${
                      isActive ? 'font-semibold text-ink' : 'text-muted hover:text-ink'
                    }`}
                  >
                    {item.name}
                  </Link>
                )
              })}
            </div>
          </div>
        </aside>

        <div>
          <div className="mb-4 flex flex-wrap items-center justify-between gap-4">
            <h1 className="font-display text-[28px] font-medium tracking-[-0.02em]">
              {title}{' '}
              <small className="font-sans text-[15px] font-normal text-muted">
                {selectedStore?.name}
              </small>
            </h1>
          </div>

          {rows.length === 0 ? (
            <div className="mx-auto max-w-[480px] px-4 py-20 text-center">
              <h2 className="mb-2 font-display text-[28px] font-medium">No products found</h2>
              <p className="text-muted">Try another category or store.</p>
            </div>
          ) : (
            <>
              <div className="grid grid-cols-2 gap-x-3 gap-y-6 sm:gap-x-5 sm:gap-y-8 xl:grid-cols-3">
                {rows.map((product) => (
                  <ProductCard key={product.id} product={product} storeName={storeName} />
                ))}
              </div>
              <div className="mt-12 flex justify-center gap-1">
                <button
                  type="button"
                  disabled={page === 1}
                  onClick={() => setPage((value) => value - 1)}
                  className="grid h-10 min-w-10 place-items-center rounded-[6px] px-2 text-[13px] font-medium transition-colors hover:bg-subtle disabled:pointer-events-none disabled:opacity-40"
                >
                  Prev
                </button>
                <span className="grid h-10 min-w-10 place-items-center rounded-[6px] bg-ink px-2 text-[13px] font-medium text-white">
                  {page}
                </span>
                <button
                  type="button"
                  disabled={rows.length < pageSize}
                  onClick={() => setPage((value) => value + 1)}
                  className="grid h-10 min-w-10 place-items-center rounded-[6px] px-2 text-[13px] font-medium transition-colors hover:bg-subtle disabled:pointer-events-none disabled:opacity-40"
                >
                  Next
                </button>
              </div>
            </>
          )}
        </div>
      </div>
    </Container>
  )
}
