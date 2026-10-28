import { Link } from 'react-router-dom'
import { Minus, Plus, Trash2 } from 'lucide-react'
import { ProductImage } from '@/components/catalog/ProductImage'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { useCartPage } from '@/hooks/useCartPage'
import { formatUsd } from '@/lib/money/formatUsd'
import { paths, productPath } from '@/paths'

export function CartPage() {
  const cart = useCartPage()

  return (
    <Container className="py-8">
      <h1 className="mb-8 font-display text-[36px] font-medium">Cart</h1>
      {cart.isEmpty ? (
        <div className="rounded-[8px] border border-line bg-surface p-10 text-center">
          <p className="text-muted">Your cart is empty.</p>
          <Button asChild className="mt-6 h-11 rounded-sm">
            <Link to={paths.home}>Continue shopping</Link>
          </Button>
        </div>
      ) : (
        <div className="grid gap-10 lg:grid-cols-[1fr_320px]">
          <ul className="space-y-4">
            {cart.lines.map((line) => (
              <li
                key={`${line.storeId}:${line.productId}`}
                className="flex gap-4 rounded-[8px] border border-line bg-surface p-4"
              >
                <Link
                  to={productPath(line.productId)}
                  className="relative size-24 shrink-0 overflow-hidden rounded-[6px] bg-subtle"
                >
                  <ProductImage name={line.name} slug={line.slug} imageUrl={line.imageUrl} />
                </Link>
                <div className="min-w-0 flex-1">
                  <Link
                    to={productPath(line.productId)}
                    className="font-medium text-ink hover:text-brand"
                  >
                    {line.name}
                  </Link>
                  <p className="mt-1 text-[13px] text-muted">SKU {line.sku}</p>
                  <p className="mt-2 text-[15px] font-semibold">{formatUsd(line.priceCents)}</p>
                  <div className="mt-3 flex flex-wrap items-center gap-2">
                    <Button
                      type="button"
                      variant="outline"
                      size="icon"
                      className="size-8"
                      aria-label="Decrease quantity"
                      onClick={() =>
                        cart.setQuantity(line.productId, line.storeId, line.quantity - 1)
                      }
                    >
                      <Minus className="size-4" />
                    </Button>
                    <span className="min-w-[2rem] text-center text-[15px] font-medium">
                      {line.quantity}
                    </span>
                    <Button
                      type="button"
                      variant="outline"
                      size="icon"
                      className="size-8"
                      aria-label="Increase quantity"
                      onClick={() =>
                        cart.setQuantity(line.productId, line.storeId, line.quantity + 1)
                      }
                    >
                      <Plus className="size-4" />
                    </Button>
                    <Button
                      type="button"
                      variant="ghost"
                      className="ml-auto text-muted hover:text-danger"
                      onClick={() => cart.removeLine(line.productId, line.storeId)}
                    >
                      <Trash2 className="size-4" />
                      Remove
                    </Button>
                  </div>
                </div>
              </li>
            ))}
          </ul>
          <aside className="h-fit rounded-[8px] border border-line bg-surface p-6">
            <h2 className="text-[13px] font-semibold tracking-[0.08em] uppercase">Summary</h2>
            <div className="mt-4 flex justify-between text-[15px]">
              <span className="text-muted">Subtotal ({cart.itemCount} items)</span>
              <span className="font-semibold">{cart.subtotalLabel}</span>
            </div>
            <Button asChild className="mt-6 h-11 w-full rounded-sm">
              <Link to={cart.checkoutPath}>Proceed to checkout</Link>
            </Button>
          </aside>
        </div>
      )}
    </Container>
  )
}
