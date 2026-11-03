import { Link } from 'react-router-dom'
import { CheckoutSteps } from '@/components/checkout/CheckoutSteps'
import { OrderConfirmationBanner } from '@/components/order/OrderConfirmationBanner'
import { OrderStatusBadge } from '@/components/order/OrderStatusBadge'
import { OrderTimeline } from '@/components/order/OrderTimeline'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { PageLoading } from '@/components/ui/loading-screen'
import { useOrderConfirmationPage } from '@/hooks/useOrderConfirmationPage'
import { formatOrderMoney, orderStatusLabel } from '@/lib/order/orderStatus'
import { orderPath, paths } from '@/paths'
import { apiErrorMessage } from '@/api/http'

export function OrderConfirmationPage() {
  const page = useOrderConfirmationPage()

  if (page.needsAuth) {
    return (
      <Container className="py-16 text-center">
        <p className="text-muted">Sign in to view this order.</p>
      </Container>
    )
  }

  if (page.invalidId) {
    return (
      <Container className="py-16 text-center">
        <p className="text-muted">Invalid order.</p>
        <Button asChild variant="link" className="mt-4">
          <Link to={paths.orders}>Back to orders</Link>
        </Button>
      </Container>
    )
  }

  if (page.query.isPending || !page.order || !page.banner || !page.timeline) {
    return <PageLoading />
  }

  if (page.query.isError) {
    return (
      <Container className="py-16 text-center">
        <p className="text-danger">{apiErrorMessage(page.query.error)}</p>
        <Button asChild variant="link" className="mt-4">
          <Link to={paths.orders}>Back to orders</Link>
        </Button>
      </Container>
    )
  }

  const order = page.order
  const placedLabel = order.created_at
    ? new Date(order.created_at).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
      })
    : '—'

  return (
    <Container className="py-8 pb-16">
      <CheckoutSteps active="confirmation" />

      <OrderConfirmationBanner
        tone={page.banner.tone}
        title={page.banner.title}
        body={page.banner.body}
        email={page.profileEmail}
      />

      <div className="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div className="flex flex-col gap-6">
          <section className="checkout-panel">
            <h2 className="checkout-panel-title">Status</h2>
            <OrderTimeline steps={page.timeline.steps} />
          </section>

          <section className="checkout-panel">
            <h2 className="checkout-panel-title">Items</h2>
            <ul>
              {order.items.map((item) => (
                <li key={`${item.product_id}-${item.sku}`} className="checkout-summary-item">
                  <span className="name">{item.product_name}</span>
                  <span className="muted">×{item.quantity}</span>
                  <b>{formatOrderMoney(item.price_cents * item.quantity, order.currency)}</b>
                </li>
              ))}
            </ul>
          </section>
        </div>

        <aside className="checkout-summary sticky top-24 h-fit">
          <h2 className="font-display text-[20px] font-medium">Order #{order.id}</h2>
          <div className="checkout-info-row mt-4 border-t border-line-strong pt-0">
            <span className="text-muted">Placed</span>
            <span>{placedLabel}</span>
          </div>
          <div className="checkout-info-row border-line-strong">
            <span className="text-muted">Status</span>
            <OrderStatusBadge status={order.status} />
          </div>
          <div className="checkout-info-row border-line-strong">
            <span className="text-muted">Payment</span>
            <span>Card · Stripe</span>
          </div>
          <div className="checkout-summary-total">
            <span>Total</span>
            <span>{formatOrderMoney(order.total_cents, order.currency)}</span>
          </div>
          {page.canCancel ? (
            <>
              <Button
                type="button"
                variant="outline"
                className="mt-5 h-11 w-full border-danger text-danger hover:bg-danger-soft"
                disabled={page.cancel.isPending}
                onClick={() => page.cancel.mutate()}
              >
                Cancel order
              </Button>
              <p className="mt-2 text-center text-[12px] text-muted">
                You can cancel until the order is shipped.
              </p>
            </>
          ) : null}
          <Button asChild variant="link" className="mt-4 w-full">
            <Link to={orderPath(order.id)}>View full order details</Link>
          </Button>
          <p className="mt-2 text-center text-[12px] text-muted">
            Status: {orderStatusLabel(order.status)}
          </p>
        </aside>
      </div>
    </Container>
  )
}
