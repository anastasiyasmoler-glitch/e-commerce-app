import { Link } from 'react-router-dom'
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
import { useCategoryPage } from '@/hooks/useCategoryPage'
import { categoryPath, paths } from '@/paths'

export function CategoryPage() {
  const catalog = useCategoryPage()

  if (catalog.status === 'loading') {
    return <PageLoading />
  }

  if (catalog.status === 'no-store') {
    return (
      <Container className="py-20 text-center">
        <h2 className="font-display text-[28px] font-medium">No stores in the catalog yet</h2>
        <p className="mt-2 text-[15px] text-muted">Add catalog data, then refresh this page.</p>
      </Container>
    )
  }

  if (catalog.status === 'error') {
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
            {catalog.current ? (
              <BreadcrumbLink asChild>
                <Link to={paths.catalog}>Catalog</Link>
              </BreadcrumbLink>
            ) : (
              <BreadcrumbPage>Catalog</BreadcrumbPage>
            )}
          </BreadcrumbItem>
          {catalog.current ? (
            <>
              <BreadcrumbSeparator />
              <BreadcrumbItem>
                <BreadcrumbPage>{catalog.current.name}</BreadcrumbPage>
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
              {catalog.facetLinks.map((item) => {
                const isActive = (item.slug ?? undefined) === catalog.slug
                return (
                  <Link
                    key={item.slug ?? 'all'}
                    to={item.slug ? categoryPath(item.slug) : paths.catalog}
                    onClick={() => catalog.setPage(1)}
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
              {catalog.title}{' '}
              <small className="font-sans text-[15px] font-normal text-muted">
                {catalog.selectedStore?.name}
              </small>
            </h1>
          </div>

          {catalog.rows.length === 0 ? (
            <div className="mx-auto max-w-[480px] px-4 py-20 text-center">
              <h2 className="mb-2 font-display text-[28px] font-medium">No products found</h2>
              <p className="text-muted">Try another category or store.</p>
            </div>
          ) : (
            <>
              <div className="grid grid-cols-2 gap-x-3 gap-y-6 sm:gap-x-5 sm:gap-y-8 xl:grid-cols-3">
                {catalog.rows.map((product) => (
                  <ProductCard key={product.id} product={product} storeName={catalog.storeName} />
                ))}
              </div>
              <div className="mt-12 flex justify-center gap-1">
                <button
                  type="button"
                  disabled={catalog.page === 1}
                  onClick={() => catalog.setPage((value) => value - 1)}
                  className="grid h-10 min-w-10 place-items-center rounded-[6px] px-2 text-[13px] font-medium transition-colors hover:bg-subtle disabled:pointer-events-none disabled:opacity-40"
                >
                  Prev
                </button>
                <span className="grid h-10 min-w-10 place-items-center rounded-[6px] bg-ink px-2 text-[13px] font-medium text-white">
                  {catalog.page}
                </span>
                <button
                  type="button"
                  disabled={catalog.rows.length < catalog.pageSize}
                  onClick={() => catalog.setPage((value) => value + 1)}
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
