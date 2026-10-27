import { Link } from 'react-router-dom'
import type { RecommendedProduct } from '@/api/recommendationApi'
import { ProductImage } from '@/components/catalog/ProductImage'
import { Button } from '@/components/ui/button'
import { productPath } from '@/paths'

type ChatRecommendedProductProps = {
  product: RecommendedProduct
  storeName: string
}

export function ChatRecommendedProduct({ product, storeName }: ChatRecommendedProductProps) {
  const price = product.price_cents

  return (
    <article className="flex gap-3 rounded-[8px] border border-line bg-surface p-3 sm:gap-4 sm:p-4">
      <Link
        to={productPath(product.product_id)}
        className="relative block size-[72px] shrink-0 overflow-hidden rounded-[6px] bg-subtle sm:size-[88px] [&_svg]:block [&_svg]:size-full"
      >
        <ProductImage name={product.name} slug={product.slug} imageUrl={product.image_url} />
      </Link>
      <div className="flex min-w-0 flex-1 flex-col justify-center gap-1">
        <div className="text-[11px] font-semibold tracking-[0.08em] text-muted uppercase">
          {storeName}
        </div>
        <Link
          to={productPath(product.product_id)}
          className="line-clamp-2 text-[15px] leading-snug font-medium text-ink underline-offset-[3px] hover:underline"
        >
          {product.name}
        </Link>
        {price !== null && price > 0 ? (
          <div className="text-[14px] font-semibold text-ink">
            $
            {(price / 100).toLocaleString(undefined, {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
            })}
          </div>
        ) : null}
        <Button
          variant="outline"
          className="mt-2 h-8 w-fit rounded-[6px] border-ink px-3 text-[12px] font-semibold"
          asChild
        >
          <Link to={productPath(product.product_id)}>View product</Link>
        </Button>
      </div>
    </article>
  )
}
