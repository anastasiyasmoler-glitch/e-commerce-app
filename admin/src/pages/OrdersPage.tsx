import { ChevronLeft, ChevronRight, Eye } from 'lucide-react'
import { apiErrorMessage } from '@/api/http'
import { useOrdersPage } from '@/hooks/useOrdersPage'
import { PageHeader } from '@/components/ui/page-header'
import { Skeleton } from '@/components/ui/skeleton'
import { StatusBadge } from '@/components/ui/status-badge'

export function OrdersPage() {
  const ordersPage = useOrdersPage()

  return (
    <>
      <PageHeader
        title="Orders"
        actions={
          <select
            className="select"
            aria-label="Status"
            value={ordersPage.status}
            onChange={(event) => ordersPage.setStatus(event.target.value)}
          >
            {ordersPage.statusOptions.map((item) => (
              <option key={item.value} value={item.value}>
                {item.label}
              </option>
            ))}
          </select>
        }
      />
      {ordersPage.orders.isError ? (
        <p className="muted" style={{ padding: 'var(--space-6) 0' }}>
          {apiErrorMessage(ordersPage.orders.error)}
        </p>
      ) : null}
      <div className="table-container">
        <table className="data-table">
          <thead>
            <tr>
              <th>Order</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Total</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {ordersPage.orders.isPending
              ? Array.from({ length: 8 }, (_, index) => (
                  <tr key={index}>
                    <td colSpan={6}>
                      <Skeleton className="h-6 w-full" />
                    </td>
                  </tr>
                ))
              : null}
            {!ordersPage.orders.isPending && !ordersPage.orders.isError && ordersPage.rows.length === 0 ? (
              <tr>
                <td colSpan={6} className="muted">
                  No orders found.
                </td>
              </tr>
            ) : null}
            {ordersPage.rows.map((order) => (
              <tr key={order.id}>
                <td>
                  <b>{order.id}</b>
                </td>
                <td>{order.customer}</td>
                <td>{order.date}</td>
                <td>
                  <b>{order.total}</b>
                </td>
                <td>
                  <StatusBadge label={order.status} />
                </td>
                <td>
                  <button type="button" className="table-action">
                    <Eye size={14} aria-hidden />
                    View
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        <div className="table-footer">
          <span>
            Showing {ordersPage.rows.length} order{ordersPage.rows.length === 1 ? '' : 's'}
          </span>
          <nav className="pagination" aria-label="Pagination">
            <a href="#" aria-label="Previous" onClick={(event) => event.preventDefault()}>
              <ChevronLeft size={16} aria-hidden />
            </a>
            <a href="#" className="on" onClick={(event) => event.preventDefault()}>
              1
            </a>
            <a href="#" onClick={(event) => event.preventDefault()}>
              2
            </a>
            <a href="#" onClick={(event) => event.preventDefault()}>
              3
            </a>
            <span>…</span>
            <a href="#" onClick={(event) => event.preventDefault()}>
              9
            </a>
            <a href="#" aria-label="Next" onClick={(event) => event.preventDefault()}>
              <ChevronRight size={16} aria-hidden />
            </a>
          </nav>
        </div>
      </div>
    </>
  )
}
