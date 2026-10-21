import { ArrowRight, Sparkles } from 'lucide-react'
import { Link } from 'react-router-dom'
import { artForCategory } from '@/catalog/art'
import { useSelectedStore } from '@/catalog/SelectedStoreContext'
import { FurnitureArt } from '@/components/catalog/FurnitureArt'
import { ProductCard } from '@/components/catalog/ProductCard'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { PageLoading } from '@/components/ui/loading-screen'
import { useCategories } from '@/hooks/useCategories'
import { useProducts } from '@/hooks/useProducts'
import { categoryPath, paths } from '@/paths'

export function HomePage() {
  const { stores, selectedStore, selectStore, isPending: storesPending } = useSelectedStore()
  const storeId = selectedStore?.id

  const categoriesQuery = useCategories(storeId)
  const latestQuery = useProducts(storeId, { section: 'latest', per_page: 4, page: 1 })
  const moreQuery = useProducts(storeId, { section: 'more', per_page: 4, page: 2 })

  const categories = (categoriesQuery.data ?? []).filter((category) => category.parent_id === null)
  const storeName = selectedStore?.name ?? ''
  const firstCategory = categories[0]
  const catalogReady = storeId !== undefined
  const loading =
    storesPending ||
    (catalogReady &&
      (categoriesQuery.isLoading || latestQuery.isLoading || moreQuery.isLoading))

  if (loading) {
    return <PageLoading />
  }

  if (!catalogReady) {
    return (
      <Container className="py-20 text-center">
        <h2 className="font-display text-[28px] font-medium">No stores in the catalog yet</h2>
        <p className="mt-2 text-[15px] text-muted">
          Seed the Catalog service or add a store in Filament, then refresh this page.
        </p>
      </Container>
    )
  }

  if (categoriesQuery.isError || latestQuery.isError || moreQuery.isError) {
    return (
      <Container className="py-20 text-center">
        <h2 className="font-display text-[28px] font-medium">Catalog could not be loaded</h2>
        <p className="mt-2 text-[15px] text-muted">Please try again later.</p>
      </Container>
    )
  }

  return (
    <Container className="pb-4">
      <section className="mt-6 grid min-h-[520px] grid-cols-1 overflow-hidden rounded-[12px] bg-subtle lg:grid-cols-2">
        <div className="flex flex-col items-start justify-center p-6 sm:p-8 lg:p-16">
          <span className="text-[12px] font-semibold tracking-[0.12em] text-muted uppercase">
            Furniture & home goods
          </span>
          <h1 className="my-4 font-display text-[36px] leading-[1.04] font-medium tracking-[-0.03em] sm:text-[48px] lg:text-[60px]">
            Everything for a considered home
          </h1>
          <p className="mb-8 max-w-[420px] text-[18px] text-muted">
            Browse furniture and home goods from independent stores, all in one place.
          </p>
          <div className="flex flex-wrap gap-3">
            <Button
              className="h-[52px] rounded-[6px] bg-brand px-8 text-[16px] font-semibold text-white hover:bg-brand-dark"
              asChild
            >
              <Link to={firstCategory ? categoryPath(firstCategory.slug) : paths.catalog}>
                Browse catalog
              </Link>
            </Button>
            <Button
              type="button"
              variant="outline"
              className="h-[52px] rounded-[6px] border-ink bg-transparent px-8 text-[16px] font-semibold text-ink hover:bg-ink hover:text-white"
            >
              <Sparkles className="size-[18px]" />
              Ask the assistant
            </Button>
          </div>
        </div>
        <div className="relative grid min-h-[300px] place-items-end justify-center bg-subtle/80 [&_svg]:-mb-[6%] [&_svg]:h-auto [&_svg]:w-[86%]">
          <FurnitureArt kind="hero" label="sofa" />
        </div>
      </section>

      <section className="mt-12 sm:mt-20">
        <div className="mb-6 flex items-end justify-between gap-4">
          <h2 className="font-display text-[22px] font-medium tracking-[-0.02em] sm:text-[28px]">
            Shop by category
          </h2>
          <Link
            to={paths.catalog}
            className="inline-flex items-center gap-1.5 border-b border-transparent text-[13px] font-semibold text-brand hover:border-current"
          >
            All categories <ArrowRight className="size-4" />
          </Link>
        </div>
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 xl:grid-cols-6">
          {categories.map((category) => (
            <Link key={category.id} to={categoryPath(category.slug)} className="group block">
              <div className="relative aspect-square overflow-hidden rounded-[8px] bg-subtle transition-shadow duration-250 group-hover:shadow-md [&_svg]:block [&_svg]:size-full">
                <FurnitureArt kind={artForCategory(category.slug)} label={category.name} />
              </div>
              <div className="mt-3 font-semibold text-ink">{category.name}</div>
            </Link>
          ))}
        </div>
      </section>

      <section className="mt-12 sm:mt-20">
        <div className="mb-6 flex items-end justify-between gap-4">
          <h2 className="font-display text-[22px] font-medium tracking-[-0.02em] sm:text-[28px]">
            Latest products
          </h2>
          <Link
            to={paths.catalog}
            className="inline-flex items-center gap-1.5 border-b border-transparent text-[13px] font-semibold text-brand hover:border-current"
          >
            View all <ArrowRight className="size-4" />
          </Link>
        </div>
        <div className="grid grid-cols-2 gap-x-3 gap-y-6 sm:gap-x-5 sm:gap-y-8 lg:grid-cols-3 xl:grid-cols-4">
          {(latestQuery.data ?? []).map((product) => (
            <ProductCard key={product.id} product={product} storeName={storeName} />
          ))}
        </div>
      </section>

      <section className="mt-12 sm:mt-20">
        <div className="grid grid-cols-1 items-center gap-10 rounded-[12px] bg-brand p-8 text-white lg:grid-cols-[1fr_auto] lg:px-16 lg:py-12">
          <div>
            <h2 className="max-w-[520px] font-display text-[36px] font-medium tracking-[-0.02em]">
              Describe it, we'll find it
            </h2>
            <p className="mt-3 mb-6 max-w-[480px] text-white/75">
              Tell the assistant what you are looking for. It suggests products that exist in the catalog.
            </p>
            <Button
              type="button"
              variant="light"
              className="h-[52px] rounded-[6px] bg-white px-8 text-[16px] font-semibold text-brand hover:bg-brand-soft"
            >
              Open AI Assistant <ArrowRight className="size-[18px]" />
            </Button>
          </div>
          <div className="flex items-center gap-3 rounded-[8px] border border-white/25 px-6 py-4 text-[16px] lg:min-w-[340px]">
            <Sparkles className="size-5 opacity-80" />
            “I want a pink sofa”
          </div>
        </div>
      </section>

      <section className="mt-12 sm:mt-20">
        <div className="mb-6 flex items-end justify-between gap-4">
          <h2 className="font-display text-[22px] font-medium tracking-[-0.02em] sm:text-[28px]">
            Stores
          </h2>
        </div>
        <div className="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
          {stores.map((store) => {
            const active = store.id === selectedStore?.id
            return (
              <button
                key={store.id}
                type="button"
                onClick={() => selectStore(store)}
                className={`flex w-full items-center gap-4 rounded-[8px] border bg-surface p-5 text-left transition-colors ${
                  active ? 'border-ink' : 'border-line hover:border-ink'
                }`}
              >
                <div className="grid size-12 shrink-0 place-items-center rounded-[6px] bg-brand font-display text-[22px] text-white">
                  {store.name.charAt(0)}
                </div>
                <div>
                  <div className="leading-[1.3] font-semibold text-ink">{store.name}</div>
                  <div className="text-[13px] text-muted">
                    {active ? 'Currently viewing' : 'View store'}
                  </div>
                </div>
              </button>
            )
          })}
        </div>
      </section>

      <section className="mt-12 sm:mt-20">
        <div className="mb-6 flex items-end justify-between gap-4">
          <h2 className="font-display text-[22px] font-medium tracking-[-0.02em] sm:text-[28px]">
            More to explore
          </h2>
        </div>
        <div className="grid grid-cols-2 gap-x-3 gap-y-6 sm:gap-x-5 sm:gap-y-8 lg:grid-cols-3 xl:grid-cols-4">
          {(moreQuery.data ?? []).map((product) => (
            <ProductCard key={product.id} product={product} storeName={storeName} />
          ))}
        </div>
      </section>
    </Container>
  )
}
