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
