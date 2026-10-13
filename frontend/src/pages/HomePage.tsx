import { useQuery } from '@tanstack/react-query'
import { ArrowRight, Sparkles } from 'lucide-react'
import { FurnitureArt } from '../components/catalog/FurnitureArt'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Container } from '@/components/ui/container'
import { Skeleton } from '@/components/ui/skeleton'
import { catalogKeys, fetchCategories, fetchProducts, type CatalogProduct } from '@/api/catalogApi'
import { useSelectedStore } from '@/catalog/SelectedStoreContext'

type ArtKind = 'sofa' | 'bed' | 'table' | 'chair' | 'storage' | 'lamp' | 'linen' | 'vase' | 'hero'

const categoryArtBySlug: Record<string, ArtKind> = {
  sofas: 'sofa',
  'straight-sofas': 'sofa',
  'corner-sofas': 'sofa',
  beds: 'bed',
  tables: 'table',
  chairs: 'chair',
  chandeliers: 'lamp',
  'floor-lamps': 'lamp',
  'table-lamps': 'lamp',
  'wall-lights': 'lamp',
  bedding: 'linen',
  'blankets-throws': 'linen',
  curtains: 'linen',
  towels: 'linen',
  wardrobes: 'storage',
  shelving: 'storage',
  decor: 'vase',
}

function artForCategory(slug: string): ArtKind {
  return categoryArtBySlug[slug] ?? 'vase'
}

function ProductCard({ product, art }: { product: CatalogProduct; art: ArtKind }) {
  const inStock = product.stock.quantity > 0

  return (
    <Card className="rounded-lg py-0">
      <div className="aspect-square bg-subtle">
        <FurnitureArt kind={art} label={product.name} />
      </div>
      <CardContent className="p-4">
        <div className="text-[12px] font-semibold tracking-wide text-muted uppercase">{product.sku}</div>
        <div className="mt-1 text-[15px] font-semibold">{product.name}</div>
        <div className="mt-3 mb-3 flex items-center justify-between">
          <span className="text-[14px] text-muted">
            {inStock ? `${product.stock.quantity} in stock` : 'Out of stock'}
          </span>
          <Badge variant={inStock ? 'secondary' : 'destructive'}>{inStock ? 'In stock' : 'Out of stock'}</Badge>
        </div>
        {inStock ? (
          <Button type="button" variant="outline" className="w-full rounded-sm">
            Add to cart
          </Button>
        ) : (
          <Button type="button" disabled className="w-full rounded-sm">
            Out of stock
          </Button>
        )}
      </CardContent>
    </Card>
  )
}

function ProductCardSkeleton() {
  return (
    <Card className="rounded-lg py-0">
      <Skeleton className="aspect-square w-full rounded-none" />
      <CardContent className="grid gap-2 p-4">
        <Skeleton className="h-3 w-16" />
        <Skeleton className="h-4 w-full" />
        <Skeleton className="h-4 w-24" />
        <Skeleton className="h-9 w-full" />
      </CardContent>
    </Card>
  )
}

function artForProduct(product: CatalogProduct): ArtKind {
  const slug = product.slug
  if (slug.includes('sofa')) return 'sofa'
  if (slug.includes('bed')) return 'bed'
  if (slug.includes('table')) return 'table'
  if (slug.includes('chair')) return 'chair'
  if (slug.includes('lamp') || slug.includes('light')) return 'lamp'
  if (slug.includes('linen') || slug.includes('bedding') || slug.includes('blanket') || slug.includes('curtain') || slug.includes('towel')) return 'linen'
  if (slug.includes('wardrobe') || slug.includes('shelf') || slug.includes('storage') || slug.includes('dresser')) return 'storage'
  return 'vase'
}

export function HomePage() {
  const { stores, selectedStore, selectStore, isLoading: storesLoading } = useSelectedStore()

  const categoriesQuery = useQuery({
    queryKey: catalogKeys.categories(selectedStore?.id),
    queryFn: () => fetchCategories(selectedStore!.id, null),
    enabled: selectedStore !== null,
  })
  const categories = (categoriesQuery.data ?? []).filter((category) => category.parent_id === null)

  const latestQuery = useQuery({
    queryKey: catalogKeys.products(selectedStore?.id, { section: 'latest' }),
    queryFn: () => fetchProducts(selectedStore!.id, { per_page: 4 }),
    enabled: selectedStore !== null,
  })

  const moreQuery = useQuery({
    queryKey: catalogKeys.products(selectedStore?.id, { section: 'more', page: 2 }),
    queryFn: () => fetchProducts(selectedStore!.id, { per_page: 4, page: 2 }),
    enabled: selectedStore !== null,
  })

  const latest = latestQuery.data ?? []
  const more = moreQuery.data ?? []

  return (
    <Container>
      <section className="grid items-center gap-10 py-12 md:grid-cols-2">
        <div>
          <span className="text-[12px] font-semibold tracking-[0.12em] text-muted uppercase">Furniture & home goods</span>
          <h1 className="font-display mt-3 text-[48px] leading-[1.15] font-medium tracking-tight">Everything for a considered home</h1>
          <p className="mt-4 max-w-md text-[16px] text-muted">Browse furniture and home goods from independent stores, all in one place.</p>
          <div className="mt-8 flex flex-wrap gap-3">
            <Button type="button" className="h-[52px] rounded-sm px-8 text-[16px]">
              Browse catalog
            </Button>
            <Button type="button" variant="outline" className="h-[52px] rounded-sm px-8 text-[16px]">
              <Sparkles />
              Ask the assistant
            </Button>
          </div>
        </div>
        <div className="aspect-square rounded-lg bg-subtle">
          <FurnitureArt kind="hero" label="sofa" />
        </div>
      </section>

      <section className="py-10">
        <div className="mb-6 flex items-end justify-between">
          <h2 className="font-display text-[28px] font-medium">Shop by category</h2>
          <span className="inline-flex items-center gap-1 text-[13px] font-semibold text-brand">
            All categories <ArrowRight size={16} />
          </span>
        </div>
        {categoriesQuery.isLoading ? (
          <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            {Array.from({ length: 6 }, (_, index) => (
              <Skeleton key={index} className="aspect-[3/4] w-full" />
            ))}
          </div>
        ) : categoriesQuery.isError ? (
          <p className="text-[15px] text-muted">Categories could not be loaded. Please try again later.</p>
        ) : categories.length === 0 ? (
          <p className="text-[15px] text-muted">No categories yet.</p>
        ) : (
          <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            {categories.map((category) => (
              <Card key={category.id} className="overflow-hidden rounded-lg py-0">
                <div className="aspect-square bg-subtle">
                  <FurnitureArt kind={artForCategory(category.slug)} label={category.name} />
                </div>
                <div className="p-3">
                  <div className="font-semibold">{category.name}</div>
                </div>
              </Card>
            ))}
          </div>
        )}
      </section>

      <section className="py-10">
        <div className="mb-6 flex items-end justify-between">
          <h2 className="font-display text-[28px] font-medium">Latest products</h2>
          <span className="inline-flex items-center gap-1 text-[13px] font-semibold text-brand">
            View all <ArrowRight size={16} />
          </span>
        </div>
        {latestQuery.isLoading ? (
          <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {Array.from({ length: 4 }, (_, index) => (
              <ProductCardSkeleton key={index} />
            ))}
          </div>
        ) : latestQuery.isError ? (
          <p className="text-[15px] text-muted">Products could not be loaded. Please try again later.</p>
        ) : (
          <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {latest.map((product) => (
              <ProductCard key={product.id} product={product} art={artForProduct(product)} />
            ))}
          </div>
        )}
      </section>

      <section className="py-10">
        <div className="grid items-center gap-8 rounded-lg bg-brand px-8 py-10 text-white md:grid-cols-2">
          <div>
            <h2 className="font-display text-[28px] font-medium">Describe it, we'll find it</h2>
            <p className="mt-2 text-[15px] text-white/80">Tell the assistant what you are looking for. It suggests products that exist in the catalog.</p>
            <Button type="button" variant="light" className="mt-6 h-[52px] rounded-sm px-8 text-[16px]">
              Open AI Assistant <ArrowRight />
            </Button>
          </div>
          <div className="flex items-center gap-3 rounded-sm bg-white/10 px-5 py-4 text-[16px]">
            <Sparkles size={18} />
            “I want a pink sofa”
          </div>
        </div>
      </section>

      <section className="py-10">
        <h2 className="font-display mb-6 text-[28px] font-medium">Stores</h2>
        {storesLoading ? (
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {Array.from({ length: 4 }, (_, index) => (
              <Skeleton key={index} className="h-[72px] w-full" />
            ))}
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {stores.map((store) => {
              const active = store.id === selectedStore?.id
              return (
                <Card
                  key={store.id}
                  role="button"
                  tabIndex={0}
                  onClick={() => selectStore(store)}
                  onKeyDown={(event) => {
                    if (event.key === 'Enter' || event.key === ' ') selectStore(store)
                  }}
                  className={`flex cursor-pointer flex-row items-center gap-4 rounded-lg p-4 ${active ? 'ring-2 ring-brand' : ''}`}
                >
                  <div className="flex size-12 items-center justify-center rounded-full bg-brand-soft font-semibold text-brand">
                    {store.name.charAt(0)}
                  </div>
                  <div>
                    <div className="font-semibold">{store.name}</div>
                    <div className="text-[13px] text-muted">{active ? 'Currently viewing' : 'View store'}</div>
                  </div>
                </Card>
              )
            })}
          </div>
        )}
      </section>

      <section className="py-10">
        <h2 className="font-display mb-6 text-[28px] font-medium">More to explore</h2>
        {moreQuery.isLoading ? (
          <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {Array.from({ length: 4 }, (_, index) => (
              <ProductCardSkeleton key={index} />
            ))}
          </div>
        ) : moreQuery.isError ? (
          <p className="text-[15px] text-muted">Products could not be loaded. Please try again later.</p>
        ) : (
          <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {more.map((product) => (
              <ProductCard key={product.id} product={product} art={artForProduct(product)} />
            ))}
          </div>
        )}
      </section>
    </Container>
  )
}
