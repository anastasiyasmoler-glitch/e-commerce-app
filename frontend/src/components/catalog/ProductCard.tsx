import { Link } from 'react-router-dom'
import type { CatalogProduct } from '@/api/catalogApi'
import { artForProductSlug } from '@/catalog/art'
import { FurnitureArt } from '@/components/catalog/FurnitureArt'
import { Button } from '@/components/ui/button'
import { productPath } from '@/paths'

export function ProductCard({ product, storeName }: { product: CatalogProduct; storeName: string }) {
  const inStock = product.stock.quantity > 0

  return (
    <article className="group flex flex-col">
      <Link
        to={productPath(product.id)}
        className="relative block aspect-square overflow-hidden rounded-[8px] bg-subtle [&_svg]:block [&_svg]:size-full [&_svg]:transition-transform [&_svg]:duration-400 group-hover:[&_svg]:scale-[1.04]"
      >
        <FurnitureArt kind={artForProductSlug(product.slug)} label={product.name} />
      </Link>
      <div className="flex flex-1 flex-col gap-1 pt-4">
        <div className="text-[12px] font-semibold tracking-[0.08em] text-muted uppercase">
          {storeName || product.sku}
        </div>
        <Link
          to={productPath(product.id)}
          className="text-[16px] leading-[1.35] font-medium text-ink underline-offset-[3px] hover:underline"
        >
          {product.name}
        </Link>
        <div className="mt-2 flex items-baseline justify-between">
          <span
            className={`inline-flex items-center gap-1.5 text-[13px] ${
              inStock ? 'text-success' : 'text-danger'
            }`}
          >
            <span className="size-[7px] rounded-full bg-current" aria-hidden />
            {inStock ? 'In stock' : 'Out of stock'}
          </span>
        </div>
        {inStock ? (
          <Button
            variant="outline"
            className="mt-4 h-9 w-full rounded-[6px] border-ink bg-transparent px-4 text-[13px] font-semibold text-ink hover:bg-ink hover:text-white"
            asChild
          >
            <Link to={productPath(product.id)}>View product</Link>
          </Button>
        ) : (
          <Button
            type="button"
            disabled
            className="mt-4 h-9 w-full rounded-[6px] bg-subtle px-4 text-[13px] font-semibold text-muted"
          >
            Out of stock
          </Button>
        )}
      </div>
    </article>
  )
}
