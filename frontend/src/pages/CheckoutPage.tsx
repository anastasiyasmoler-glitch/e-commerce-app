import { Link } from 'react-router-dom'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { useCheckoutPage } from '@/hooks/useCheckoutPage'
import { formatOrderMoney } from '@/lib/order/orderStatus'
import { paths } from '@/paths'

export function CheckoutPage() {
  const checkout = useCheckoutPage()

  if (checkout.needsAuth) {
    return (
      <Container className="py-16 text-center">
        <h1 className="font-display text-[32px] font-medium">Checkout</h1>
        <p className="mt-4 text-muted">Sign in to place an order.</p>
        <Button asChild className="mt-6 rounded-[6px] bg-brand text-white hover:bg-brand-dark">
          <Link to={paths.profile}>Go to profile</Link>
        </Button>
      </Container>
    )
  }

  if (checkout.lines.length === 0) {
    return (
      <Container className="py-16 text-center">
        <h1 className="font-display text-[32px] font-medium">Checkout</h1>
        <p className="mt-4 text-muted">Your cart is empty.</p>
        <Button asChild className="mt-6 rounded-[6px] bg-brand text-white hover:bg-brand-dark">
          <Link to={paths.cart}>View cart</Link>
        </Button>
      </Container>
    )
  }

  const totalCents = checkout.lines.reduce(
    (sum, line) => sum + line.priceCents * line.quantity,
    0,
  )

  return (
    <Container className="py-8">
      <Breadcrumb className="mb-4">
        <BreadcrumbList>
          <BreadcrumbItem>
            <BreadcrumbLink asChild>
              <Link to={paths.home}>Home</Link>
            </BreadcrumbLink>
          </BreadcrumbItem>
          <BreadcrumbSeparator />
          <BreadcrumbItem>
            <BreadcrumbLink asChild>
              <Link to={paths.cart}>Cart</Link>
            </BreadcrumbLink>
          </BreadcrumbItem>
          <BreadcrumbSeparator />
          <BreadcrumbItem>
            <BreadcrumbPage>Checkout</BreadcrumbPage>
          </BreadcrumbItem>
        </BreadcrumbList>
      </Breadcrumb>

      <h1 className="mb-8 font-display text-[36px] font-medium">Checkout</h1>

      <div className="grid gap-10 lg:grid-cols-[1fr_320px]">
        <section className="rounded-[12px] border border-line p-6">
          <h2 className="mb-4 text-[13px] font-semibold tracking-[0.08em] uppercase">Items</h2>
          <ul className="divide-y divide-line">
            {checkout.lines.map((line) => (
              <li key={line.productId} className="flex justify-between gap-4 py-3 text-[15px]">
                <span>
                  {line.name} × {line.quantity}
                </span>
                <span className="font-medium">
                  {formatOrderMoney(line.priceCents * line.quantity, 'USD')}
                </span>
              </li>
            ))}
          </ul>
        </section>
        <aside className="h-fit rounded-[12px] border border-line p-6">
          <div className="flex items-baseline justify-between">
            <span className="text-muted">Total</span>
            <span className="text-[22px] font-semibold">{formatOrderMoney(totalCents, 'USD')}</span>
          </div>
          <p className="mt-3 text-[13px] text-muted">
            Payment runs in test mode after the order is created (Stripe saga).
          </p>
          <Button
            type="button"
            className="mt-6 h-12 w-full rounded-[6px] bg-brand text-[16px] font-semibold text-white hover:bg-brand-dark"
            disabled={!checkout.canCheckout || checkout.placeOrder.isPending}
            onClick={() => checkout.placeOrder.mutate()}
          >
            {checkout.placeOrder.isPending ? 'Placing order…' : 'Place order'}
          </Button>
        </aside>
      </div>
    </Container>
  )
}
