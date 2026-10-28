import { Link } from 'react-router-dom'
import type { SearchItem } from '@/api/searchApi'
import { ProductImage } from '@/components/catalog/ProductImage'
import { productImagePublicUrl } from '@/lib/catalog/productImageUrl'
import { Button } from '@/components/ui/button'
import { productPath } from '@/paths'

export function SearchProductCard({ product, storeName }: { product: SearchItem; storeName: string }) {
  const productId = Number(product.id)
  const inStock = product.available

  return (
    <article className="group flex h-full flex-col">
      <Link
        to={productPath(productId)}
        className="relative block aspect-square shrink-0 overflow-hidden rounded-[8px] bg-subtle [&_img]:size-full [&_img]:object-cover [&_img]:transition-transform [&_img]:duration-400 group-hover:[&_img]:scale-[1.04] [&_svg]:block [&_svg]:size-full [&_svg]:transition-transform [&_svg]:duration-400 group-hover:[&_svg]:scale-[1.04]"
      >
        <ProductImage
          name={product.name}
          slug={product.slug}
          imageUrl={productImagePublicUrl(product.image)}
        />
      </Link>
      <div className="flex flex-1 flex-col pt-4">
        <div className="line-clamp-1 text-[12px] font-semibold tracking-[0.08em] text-muted uppercase">
          {storeName || product.sku}
        </div>
        <Link
          to={productPath(productId)}
          className="mt-1 line-clamp-2 min-h-[2.7em] text-[16px] leading-[1.35] font-medium text-ink underline-offset-[3px] hover:underline"
        >
          {product.name}
        </Link>
        <div className="mt-2 flex min-h-[22px] items-baseline justify-between gap-2">
          {product.price > 0 ? (
            <span className="text-[15px] font-semibold text-ink">
              ${(product.price / 100).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
              })}
            </span>
          ) : (
            <span aria-hidden className="invisible text-[15px] font-semibold">$0.00</span>
          )}
          <span
            className={`inline-flex shrink-0 items-center gap-1.5 text-[13px] ${
              inStock ? 'text-success' : 'text-danger'
            }`}
          >
            <span className="size-[7px] rounded-full bg-current" aria-hidden />
            {inStock ? 'In stock' : 'Out of stock'}
          </span>
        </div>
        <div className="mt-auto pt-4">
          {inStock ? (
            <Button
              variant="outline"
              className="h-9 w-full rounded-[6px] border-ink bg-transparent px-4 text-[13px] font-semibold text-ink hover:bg-ink hover:text-white"
              asChild
            >
              <Link to={productPath(productId)}>View product</Link>
            </Button>
          ) : (
            <Button
              type="button"
              disabled
              className="h-9 w-full rounded-[6px] bg-subtle px-4 text-[13px] font-semibold text-muted"
            >
              Out of stock
            </Button>
          )}
        </div>
      </div>
    </article>
  )
}
