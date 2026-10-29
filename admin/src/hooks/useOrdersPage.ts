import { useMemo, useState } from 'react'
import { useOrders } from '@/hooks/useOrders'
import { ORDER_STATUS_FILTER_OPTIONS } from '@/lib/order/formatOrderRow'

export function useOrdersPage() {
  const [status, setStatus] = useState('all')
  const orders = useOrders()

  const rows = useMemo(
    () =>
      (orders.data ?? []).filter(
        (order) => status === 'all' || order.rawStatus === status,
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
