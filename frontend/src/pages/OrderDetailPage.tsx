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
import { useOrderDetailPage } from '@/hooks/useOrderDetailPage'
import { formatOrderMoney } from '@/lib/order/orderStatus'
import { paths } from '@/paths'
import { apiErrorMessage } from '@/api/http'

export function OrderDetailPage() {
  const detail = useOrderDetailPage()

  if (detail.needsAuth) {
    return (
      <Container className="py-16 text-center">
        <p className="text-muted">Sign in to view this order.</p>
      </Container>
    )
  }

  if (detail.invalidId) {
    return (
      <Container className="py-16 text-center">
        <p className="text-muted">Invalid order.</p>
        <Button asChild variant="link" className="mt-4">
          <Link to={paths.orders}>Back to orders</Link>
        </Button>
      </Container>
    )
  }

  if (detail.query.isPending) {
    return <PageLoading />
  }

  if (detail.query.isError || !detail.order) {
    return (
      <Container className="py-16 text-center">
        <p className="text-danger">
          {detail.query.isError ? apiErrorMessage(detail.query.error) : 'Order not found.'}
        </p>
        <Button asChild variant="link" className="mt-4">
          <Link to={paths.orders}>Back to orders</Link>
        </Button>
      </Container>
    )
  }

  const order = detail.order

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
              <Link to={paths.orders}>My orders</Link>
            </BreadcrumbLink>
          </BreadcrumbItem>
          <BreadcrumbSeparator />
          <BreadcrumbItem>
            <BreadcrumbPage>Order #{order.id}</BreadcrumbPage>
          </BreadcrumbItem>
        </BreadcrumbList>
      </Breadcrumb>

      <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
        <h1 className="font-display text-[36px] font-medium">Order #{order.id}</h1>
        <OrderStatusBadge status={order.status} />
      </div>

      <p className="mb-8 text-[15px] text-muted">
        Placed{' '}
        {order.created_at ? new Date(order.created_at).toLocaleString() : '—'}
      </p>

      <table className="mb-8 w-full border-collapse rounded-[12px] border border-line">
        <thead>
          <tr className="border-b border-line text-left text-[13px] font-semibold tracking-[0.06em] uppercase">
            <th className="p-4">Item</th>
            <th className="p-4">Qty</th>
            <th className="p-4 text-right">Price</th>
          </tr>
        </thead>
        <tbody>
          {order.items.map((item) => (
            <tr key={`${item.product_id}-${item.sku}`} className="border-t border-line text-[15px]">
              <td className="p-4">{item.product_name}</td>
              <td className="p-4">{item.quantity}</td>
              <td className="p-4 text-right">
                {formatOrderMoney(item.price_cents * item.quantity, order.currency)}
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      <div className="flex justify-end text-[18px] font-semibold">
        Total {formatOrderMoney(order.total_cents, order.currency)}
      </div>
    </Container>
  )
}
