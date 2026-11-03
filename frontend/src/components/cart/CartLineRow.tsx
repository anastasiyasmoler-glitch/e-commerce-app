import { Link } from 'react-router-dom'
import { Minus, Plus, Trash2 } from 'lucide-react'
import type { CartLine } from '@/lib/cart/types'
import { ProductImage } from '@/components/catalog/ProductImage'
import { Button } from '@/components/ui/button'
import { formatOrderMoney } from '@/lib/order/orderStatus'
import { productImagePublicUrl } from '@/lib/catalog/productImageUrl'
import { productPath } from '@/paths'

type CartLineRowProps = {
  line: CartLine
  onSetQuantity: (productId: number, quantity: number) => void
  onRemove: (productId: number) => void
}

export function CartLineRow({ line, onSetQuantity, onRemove }: CartLineRowProps) {
  const imageUrl = productImagePublicUrl(line.imageUrl) ?? line.imageUrl ?? undefined
  const unitPriceLabel = formatOrderMoney(line.priceCents, 'USD')
  const lineTotalLabel = formatOrderMoney(line.priceCents * line.quantity, 'USD')

  return (
    <li className="flex gap-4 rounded-[10px] border border-line bg-surface p-4 sm:gap-5 sm:p-5">
      <Link
        to={productPath(line.productId)}
        className="relative block size-[88px] shrink-0 overflow-hidden rounded-[8px] bg-subtle sm:size-[104px] [&_img]:size-full [&_img]:object-cover [&_svg]:size-full"
      >
        <ProductImage name={line.name} slug={line.slug} imageUrl={imageUrl} />
      </Link>

      <div className="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div className="min-w-0 flex-1">
          <Link
            to={productPath(line.productId)}
            className="line-clamp-2 text-[16px] leading-snug font-medium text-ink hover:text-brand"
          >
            {line.name}
          </Link>
          <p className="mt-1 text-[13px] text-muted">{unitPriceLabel} each</p>
        </div>

        <div className="flex flex-wrap items-center justify-between gap-3 sm:justify-end sm:gap-6">
          <div
            className="inline-flex items-center rounded-[8px] border border-line bg-bg"
            role="group"
            aria-label={`Quantity for ${line.name}`}
          >
            <Button
              type="button"
              variant="ghost"
              size="icon"
              className="size-9 rounded-none rounded-l-[7px] text-ink hover:bg-subtle"
              aria-label="Decrease quantity"
              onClick={() => onSetQuantity(line.productId, line.quantity - 1)}
            >
              <Minus className="size-4" />
            </Button>
            <span className="min-w-[2.5rem] border-x border-line px-2 text-center text-[15px] font-semibold tabular-nums">
              {line.quantity}
            </span>
            <Button
              type="button"
              variant="ghost"
              size="icon"
              className="size-9 rounded-none rounded-r-[7px] text-ink hover:bg-subtle"
              aria-label="Increase quantity"
              onClick={() => onSetQuantity(line.productId, line.quantity + 1)}
            >
              <Plus className="size-4" />
            </Button>
          </div>

          <div className="flex items-center gap-3">
            <span className="text-[17px] font-semibold tabular-nums text-ink sm:min-w-[5.5rem] sm:text-right">
              {lineTotalLabel}
            </span>
            <Button
              type="button"
              variant="ghost"
              size="icon"
              className="size-9 shrink-0 text-muted hover:bg-danger/10 hover:text-danger"
              aria-label="Remove item"
              onClick={() => onRemove(line.productId)}
            >
              <Trash2 className="size-4" />
            </Button>
          </div>
        </div>
      </div>
    </li>
  )
}
