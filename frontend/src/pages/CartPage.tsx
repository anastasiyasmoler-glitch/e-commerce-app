import { Link } from 'react-router-dom'
import { ArrowRight, ShoppingBag } from 'lucide-react'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import { CartLineRow } from '@/components/cart/CartLineRow'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { useCartPage } from '@/hooks/useCartPage'
import { paths } from '@/paths'

export function CartPage() {
  const cart = useCartPage()

  return (
    <Container className="py-8 pb-16">
      <Breadcrumb className="mb-4">
        <BreadcrumbList>
          <BreadcrumbItem>
            <BreadcrumbLink asChild>
              <Link to={paths.home}>Home</Link>
            </BreadcrumbLink>
          </BreadcrumbItem>
          <BreadcrumbSeparator />
          <BreadcrumbItem>
            <BreadcrumbPage>Cart</BreadcrumbPage>
          </BreadcrumbItem>
        </BreadcrumbList>
      </Breadcrumb>

      <div className="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 className="font-display text-[36px] font-medium tracking-[-0.02em]">Your cart</h1>
          {!cart.isEmpty ? (
            <p className="mt-2 text-[15px] text-muted">{cart.itemCountLabel}</p>
          ) : null}
        </div>
        {!cart.isEmpty ? (
          <Button
            type="button"
            variant="ghost"
            className="text-[13px] font-medium text-muted hover:text-danger"
            onClick={() => cart.clearCart()}
          >
            Clear cart
          </Button>
        ) : null}
      </div>

      {cart.isEmpty ? (
        <div className="mx-auto max-w-md rounded-[16px] border border-line bg-surface px-8 py-16 text-center">
          <div className="mx-auto grid size-16 place-items-center rounded-full bg-subtle text-brand">
            <ShoppingBag className="size-8" strokeWidth={1.5} aria-hidden />
          </div>
          <h2 className="mt-6 font-display text-[24px] font-medium">Your cart is empty</h2>
          <p className="mt-2 text-[15px] leading-relaxed text-muted">
            Add pieces you love from the catalog — they will show up here.
          </p>
          <Button
            asChild
            className="mt-8 h-11 rounded-[6px] bg-brand px-8 text-[15px] font-semibold text-white hover:bg-brand-dark"
          >
            <Link to={paths.catalog}>
              Browse catalog
              <ArrowRight className="size-4" />
            </Link>
          </Button>
        </div>
      ) : (
        <div className="grid gap-10 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
          <ul className="flex flex-col gap-3">
            {cart.lines.map((line) => (
              <CartLineRow
                key={line.productId}
                line={line}
                onSetQuantity={cart.setQuantity}
                onRemove={cart.removeLine}
              />
            ))}
          </ul>

          <aside className="lg:sticky lg:top-24">
            <div className="rounded-[12px] border border-line bg-surface p-6 shadow-[0_1px_0_rgba(0,0,0,0.04)]">
              <h2 className="font-display text-[20px] font-medium">Order summary</h2>
              <dl className="mt-5 space-y-3 text-[15px]">
                <div className="flex justify-between gap-4">
                  <dt className="text-muted">Items ({cart.itemCount})</dt>
                  <dd className="font-medium tabular-nums">{cart.subtotalLabel}</dd>
                </div>
                <div className="flex justify-between gap-4">
                  <dt className="text-muted">Shipping</dt>
                  <dd className="text-muted">At checkout</dd>
                </div>
              </dl>
              <div className="mt-5 flex items-baseline justify-between border-t border-line pt-5">
                <span className="text-[15px] font-medium text-ink">Subtotal</span>
                <span className="font-display text-[26px] font-medium tabular-nums">
                  {cart.subtotalLabel}
                </span>
              </div>
              <p className="mt-3 text-[13px] leading-relaxed text-muted">
                Tax and delivery options are calculated on the next step.
              </p>
              <Button
                asChild
                className="mt-6 h-12 w-full rounded-[6px] bg-brand text-[16px] font-semibold text-white hover:bg-brand-dark"
              >
                <Link to={paths.checkout}>
                  Proceed to checkout
                  <ArrowRight className="size-4" />
                </Link>
              </Button>
              <Button
                asChild
                variant="outline"
                className="mt-3 h-11 w-full rounded-[6px] border-line bg-transparent font-medium"
              >
                <Link to={paths.catalog}>Continue shopping</Link>
              </Button>
            </div>
          </aside>
        </div>
      )}
    </Container>
  )
}
