import { useMemo, useState } from 'react'
import { useOrders } from '@/hooks/useOrders'

export const ORDER_STATUS_FILTER_OPTIONS = [
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
] as const

export function useOrdersPage() {
  const [status, setStatus] = useState('All statuses')
  const orders = useOrders()

  const rows = useMemo(
    () =>
      (orders.data ?? []).filter(
        (order) => status === 'All statuses' || order.status === status,
      ),
    [orders.data, status],
  )

  return {
    status,
    setStatus,
    orders,
    rows,
    statusOptions: ORDER_STATUS_FILTER_OPTIONS,
  }
}
