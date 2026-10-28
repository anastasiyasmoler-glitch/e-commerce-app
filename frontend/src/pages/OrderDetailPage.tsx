import { Link } from 'react-router-dom'
import { OrderStatusBadge } from '@/components/order/OrderStatusBadge'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { PageLoading } from '@/components/ui/loading-screen'
import { useOrderDetailPage } from '@/hooks/useOrderDetailPage'
import { formatUsd } from '@/lib/money/formatUsd'
import { paths } from '@/paths'

export function OrderDetailPage() {
  const detail = useOrderDetailPage()

  if (detail.isPending) {
    return <PageLoading />
  }

  if (detail.isNotFound || !detail.order) {
    return (
      <Container className="py-20 text-center">
        <h1 className="font-display text-[28px] font-medium">Order not found</h1>
        <Button asChild className="mt-6 h-11 rounded-sm">
          <Link to={paths.orders}>Back to orders</Link>
        </Button>
      </Container>
    )
  }

  const order = detail.order
  const isCancelled = order.status === 'cancelled'
  const isPaymentFailed = order.status === 'payment_failed'

  return (
    <Container className="py-8">
      <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
          <p className="text-[13px] font-semibold tracking-[0.08em] text-brand uppercase">Order</p>
          <h1 className="font-display text-[36px] font-medium">#{order.id}</h1>
        </div>
        <OrderStatusBadge status={order.status} />
      </div>
      {(isCancelled || isPaymentFailed) && (
        <div className="mb-6 rounded-[8px] border border-line bg-subtle px-4 py-3 text-[14px] text-muted">
          {isCancelled
            ? 'This order was cancelled. Items are no longer reserved.'
            : 'Payment could not be completed. Stock may have been released.'}
        </div>
      )}
      <div className="grid gap-8 lg:grid-cols-[1fr_280px]">
        <section className="rounded-[8px] border border-line bg-surface p-6">
          <h2 className="text-[13px] font-semibold tracking-[0.08em] uppercase">Items</h2>
          <ul className="mt-4 divide-y divide-line">
            {order.items.map((item) => (
              <li
                key={`${item.store_id}:${item.product_id}`}
                className="flex justify-between gap-4 py-3 text-[15px]"
              >
                <div>
                  <p className="font-medium">{item.product_name}</p>
                  <p className="text-[13px] text-muted">
                    Qty {item.quantity}
                    {item.sku ? ` · ${item.sku}` : ''}
                  </p>
                </div>
                <span className="font-semibold">
                  {formatUsd(item.price_cents * item.quantity, order.currency)}
                </span>
              </li>
            ))}
          </ul>
        </section>
        <aside className="space-y-4">
          <div className="rounded-[8px] border border-line bg-surface p-6">
            <h2 className="text-[13px] font-semibold tracking-[0.08em] uppercase">Total</h2>
            <p className="mt-2 text-[22px] font-semibold">
              {formatUsd(order.total_cents, order.currency)}
            </p>
            {order.created_at ? (
              <p className="mt-2 text-[13px] text-muted">
                Placed {new Date(order.created_at).toLocaleString()}
              </p>
            ) : null}
          </div>
          {detail.canCancel ? (
            <Button
              type="button"
              variant="outline"
              className="h-11 w-full rounded-sm"
              disabled={detail.cancel.isPending}
              onClick={() => detail.cancel.mutate()}
            >
              {detail.cancel.isPending ? 'Cancelling…' : 'Cancel order'}
            </Button>
          ) : null}
          <Button variant="ghost" className="h-11 w-full rounded-sm" asChild>
            <Link to={paths.orders}>All orders</Link>
          </Button>
        </aside>
      </div>
    </Container>
  )
}
