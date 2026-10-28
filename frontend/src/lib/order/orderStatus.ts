const LABELS: Record<string, string> = {
  created: 'Created',
  stock_reserved: 'Stock reserved',
  payment_pending: 'Payment pending',
  paid: 'Paid',
  confirmed: 'Confirmed',
  shipped: 'Shipped',
  completed: 'Completed',
  payment_failed: 'Payment failed',
  cancelled: 'Cancelled',
}

export function orderStatusLabel(status: string): string {
  return LABELS[status] ?? status.replaceAll('_', ' ')
}

export function canCancelOrder(status: string): boolean {
  return !['shipped', 'completed', 'cancelled'].includes(status)
}
