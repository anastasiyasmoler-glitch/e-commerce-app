import type { OrderApiRecord } from '@/api/orderApi'

export type AdminOrderRow = {
  id: string
  numericId: number
  customer: string
  date: string
  total: string
  status: string
  rawStatus: string
}

const STATUS_LABELS: Record<string, string> = {
  created: 'Created',
  stock_reserved: 'Stock reserved',
  payment_pending: 'Payment pending',
  paid: 'Paid',
  confirmed: 'Confirmed',
  shipped: 'Shipped',
  completed: 'Completed',
  cancelled: 'Cancelled',
  payment_failed: 'Payment failed',
}

export function orderStatusLabel(status: string): string {
  return STATUS_LABELS[status] ?? status
}

export function formatOrderMoney(cents: number, currency: string): string {
  const amount = cents / 100
  try {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency || 'USD',
    }).format(amount)
  } catch {
    return `$${amount.toFixed(2)}`
  }
}

export function formatOrderDate(iso: string | null): string {
  if (!iso) {
    return '—'
  }

  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(new Date(iso))
}

export function mapOrderRow(order: OrderApiRecord): AdminOrderRow {
  return {
    id: `#${order.id}`,
    numericId: order.id,
    customer: `User ${order.user_id}`,
    date: formatOrderDate(order.created_at),
    total: formatOrderMoney(order.total_cents, order.currency),
    status: orderStatusLabel(order.status),
    rawStatus: order.status,
  }
}

export const ORDER_STATUS_FILTER_OPTIONS = [
  { label: 'All statuses', value: 'all' },
  { label: 'Created', value: 'created' },
  { label: 'Stock reserved', value: 'stock_reserved' },
  { label: 'Payment pending', value: 'payment_pending' },
  { label: 'Paid', value: 'paid' },
  { label: 'Confirmed', value: 'confirmed' },
  { label: 'Shipped', value: 'shipped' },
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' },
  { label: 'Payment failed', value: 'payment_failed' },
] as const
