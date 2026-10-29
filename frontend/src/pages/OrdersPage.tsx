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
import { PageLoading } from '@/components/ui/loading-screen'
import { OrderStatusBadge } from '@/components/order/OrderStatusBadge'
import { useOrdersPage } from '@/hooks/useOrdersPage'
import { formatOrderMoney } from '@/lib/order/orderStatus'
import { orderPath, paths } from '@/paths'
import { apiErrorMessage } from '@/api/http'

export function OrdersPage() {
  const orders = useOrdersPage()

  if (orders.needsAuth) {
    return (
      <Container className="py-16 text-center">
        <h1 className="font-display text-[32px] font-medium">My orders</h1>
        <p className="mt-4 text-muted">Sign in to view your orders.</p>
      </Container>
    )
  }

  if (orders.query.isPending) {
    return <PageLoading />
  }

  if (orders.query.isError) {
    return (
      <Container className="py-16 text-center">
        <p className="text-danger">{apiErrorMessage(orders.query.error)}</p>
      </Container>
    )
  }

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
            <BreadcrumbPage>My orders</BreadcrumbPage>
          </BreadcrumbItem>
        </BreadcrumbList>
      </Breadcrumb>

      <h1 className="mb-8 font-display text-[36px] font-medium">My orders</h1>

      {orders.orders.length === 0 ? (
        <p className="text-muted">You have not placed any orders yet.</p>
      ) : (
        <ul className="divide-y divide-line rounded-[12px] border border-line">
          {orders.orders.map((order) => (
            <li key={order.id}>
              <Link
                to={orderPath(order.id)}
                className="flex flex-wrap items-center justify-between gap-4 p-4 transition-colors hover:bg-subtle/80"
              >
                <div>
                  <p className="font-medium text-ink">Order #{order.id}</p>
                  <p className="text-[13px] text-muted">
                    {order.created_at
                      ? new Date(order.created_at).toLocaleString()
                      : '—'}
                  </p>
                </div>
                <div className="flex items-center gap-4">
                  <OrderStatusBadge status={order.status} />
                  <span className="font-semibold">
                    {formatOrderMoney(order.total_cents, order.currency)}
                  </span>
                </div>
              </Link>
            </li>
          ))}
        </ul>
      )}

      {orders.meta && orders.meta.last_page > 1 ? (
        <div className="mt-6 flex items-center justify-center gap-4">
          <Button
            type="button"
            variant="outline"
            disabled={orders.page <= 1}
            onClick={() => orders.setPage(orders.page - 1)}
          >
            Previous
          </Button>
          <span className="text-[13px] text-muted">
            Page {orders.meta.current_page} of {orders.meta.last_page}
          </span>
          <Button
            type="button"
            variant="outline"
            disabled={orders.page >= orders.meta.last_page}
            onClick={() => orders.setPage(orders.page + 1)}
          >
            Next
          </Button>
        </div>
      ) : null}
    </Container>
  )
}
