import { Link } from 'react-router-dom'
import { OrderStatusBadge } from '@/components/order/OrderStatusBadge'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { Skeleton } from '@/components/ui/skeleton'
import { useOrdersPage } from '@/hooks/useOrdersPage'
import { formatUsd } from '@/lib/money/formatUsd'
import { orderPath, paths } from '@/paths'

export function OrdersPage() {
  const orders = useOrdersPage()

  return (
    <Container className="py-8">
      <h1 className="mb-8 font-display text-[36px] font-medium">My orders</h1>
      {orders.isPending ? (
        <div className="space-y-3">
          <Skeleton className="h-20 w-full rounded-[8px]" />
          <Skeleton className="h-20 w-full rounded-[8px]" />
        </div>
      ) : orders.isError ? (
        <p className="text-danger">Could not load orders.</p>
      ) : orders.orders.length === 0 ? (
        <div className="rounded-[8px] border border-line bg-surface p-10 text-center">
          <p className="text-muted">You have not placed any orders yet.</p>
          <Button asChild className="mt-6 h-11 rounded-sm">
            <Link to={paths.home}>Browse catalog</Link>
          </Button>
        </div>
      ) : (
        <ul className="divide-y divide-line overflow-hidden rounded-[8px] border border-line bg-surface">
          {orders.orders.map((order) => (
            <li key={order.id}>
              <Link
                to={orderPath(order.id)}
                className="flex flex-wrap items-center justify-between gap-4 px-5 py-4 transition-colors hover:bg-brand-soft/40"
              >
                <div>
                  <p className="font-medium text-ink">Order #{order.id}</p>
                  <p className="mt-1 text-[13px] text-muted">
                    {order.created_at
                      ? new Date(order.created_at).toLocaleString()
                      : '—'}
                  </p>
                </div>
                <div className="flex flex-wrap items-center gap-3">
                  <OrderStatusBadge status={order.status} />
                  <span className="font-semibold">{formatUsd(order.total_cents, order.currency)}</span>
                </div>
              </Link>
            </li>
          ))}
        </ul>
      )}
    </Container>
  )
}
