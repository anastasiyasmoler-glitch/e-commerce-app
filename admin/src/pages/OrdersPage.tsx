import { useQuery } from '@tanstack/react-query'
import { ChevronLeft, ChevronRight, Eye } from 'lucide-react'
import { useState } from 'react'
import { adminKeys, fetchOrders } from '@/api/adminApi'
import { PageHeader } from '@/components/ui/page-header'
import { StatusBadge } from '@/components/ui/status-badge'

const statuses = [
  'All statuses',
  'Created',
  'Stock reserved',
  'Payment pending',
  'Paid',
  'Confirmed',
  'Shipped',
  'Completed',
  'Cancelled',
  'Payment failed',
]

export function OrdersPage() {
  const [status, setStatus] = useState('All statuses')
  const orders = useQuery({ queryKey: adminKeys.orders, queryFn: fetchOrders })

  const rows = (orders.data ?? []).filter(
    (order) => status === 'All statuses' || order.status === status,
  )

  return (
    <>
      <PageHeader
        title="Orders"
        actions={
          <select
            className="select"
            aria-label="Status"
            value={status}
            onChange={(event) => setStatus(event.target.value)}
          >
            {statuses.map((item) => (
              <option key={item}>{item}</option>
            ))}
          </select>
        }
      />
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
            {rows.map((order) => (
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
          <span>Showing {rows.length} of 1,248 orders</span>
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
