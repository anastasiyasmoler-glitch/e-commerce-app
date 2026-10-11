import { ArrowRight, Sparkles } from 'lucide-react'
import { FurnitureArt } from '../components/catalog/FurnitureArt'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Container } from '@/components/ui/container'

const categories = [
  { name: 'Sofas', count: '124 products', art: 'sofa' as const },
  { name: 'Beds', count: '86 products', art: 'bed' as const },
  { name: 'Tables', count: '74 products', art: 'table' as const },
  { name: 'Chairs', count: '142 products', art: 'chair' as const },
  { name: 'Storage', count: '98 products', art: 'storage' as const },
  { name: 'Lighting', count: '211 products', art: 'lamp' as const },
]

const latest = [
  { store: 'Nordic Home', name: 'Oslo 3-Seater Sofa', price: '$549', inStock: true, art: 'sofa' as const },
  { store: 'Cozy Corner', name: 'Queen Bed with Lift Storage', price: '$385', inStock: true, art: 'bed' as const },
  { store: 'Loft Studio', name: 'Extendable Dining Table, Oak', price: '$279', inStock: true, art: 'table' as const },
  { store: 'Nordic Home', name: 'Arc Floor Lamp', price: '$64', inStock: true, art: 'lamp' as const },
]

const more = [
  { store: 'Cozy Corner', name: '4-Drawer Dresser', price: '$152', inStock: false, art: 'storage' as const },
  { store: 'Nordic Home', name: 'Satin Bedding Set, Queen', price: '$48', inStock: true, art: 'linen' as const },
  { store: 'Loft Studio', name: 'Bergen Armchair, Bouclé', price: '$199', inStock: true, art: 'chair' as const },
  { store: 'Atelier Wood', name: 'Ceramic Vase, 30 cm', price: '$18', inStock: true, art: 'vase' as const },
]

const stores = [
  { name: 'Nordic Home', meta: '64 products', initial: 'N' },
  { name: 'Loft Studio', meta: '38 products', initial: 'L' },
  { name: 'Cozy Corner', meta: '52 products', initial: 'C' },
  { name: 'Atelier Wood', meta: '22 products', initial: 'A' },
]

function ProductCard({
  store,
  name,
  price,
  inStock,
  art,
}: {
  store: string
  name: string
  price: string
  inStock: boolean
  art: 'sofa' | 'bed' | 'table' | 'lamp' | 'storage' | 'linen' | 'chair' | 'vase'
}) {
  return (
    <Card className="rounded-lg py-0">
      <div className="aspect-square bg-subtle">
        <FurnitureArt kind={art} label={name} />
      </div>
      <CardContent className="p-4">
        <div className="text-[12px] font-semibold tracking-wide text-muted uppercase">{store}</div>
        <div className="mt-1 text-[15px] font-semibold">{name}</div>
        <div className="mt-3 mb-3 flex items-center justify-between">
          <span className="text-[16px] font-semibold">{price}</span>
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

export function HomePage() {
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
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
          {categories.map((item) => (
            <Card key={item.name} className="overflow-hidden rounded-lg py-0">
              <div className="aspect-square bg-subtle">
                <FurnitureArt kind={item.art} label={item.name} />
              </div>
              <div className="p-3">
                <div className="font-semibold">{item.name}</div>
                <div className="text-[13px] text-muted">{item.count}</div>
              </div>
            </Card>
          ))}
        </div>
      </section>

      <section className="py-10">
        <div className="mb-6 flex items-end justify-between">
          <h2 className="font-display text-[28px] font-medium">Latest products</h2>
          <span className="inline-flex items-center gap-1 text-[13px] font-semibold text-brand">
            View all <ArrowRight size={16} />
          </span>
        </div>
        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          {latest.map((p) => (
            <ProductCard key={p.name} {...p} />
          ))}
        </div>
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
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {stores.map((store) => (
            <Card key={store.name} className="flex flex-row items-center gap-4 rounded-lg p-4">
              <div className="flex size-12 items-center justify-center rounded-full bg-brand-soft font-semibold text-brand">{store.initial}</div>
              <div>
                <div className="font-semibold">{store.name}</div>
                <div className="text-[13px] text-muted">{store.meta}</div>
              </div>
            </Card>
          ))}
        </div>
      </section>

      <section className="py-10">
        <h2 className="font-display mb-6 text-[28px] font-medium">More to explore</h2>
        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          {more.map((p) => (
            <ProductCard key={p.name} {...p} />
          ))}
        </div>
      </section>
    </Container>
  )
}
