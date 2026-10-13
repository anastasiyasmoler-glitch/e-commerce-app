const STATUS_CLASS: Record<string, string> = {
  Paid: 'status-success',
  Confirmed: 'status-success',
  Completed: 'status-success',
  Sent: 'status-success',
  sent: 'status-success',
  Active: 'status-success',
  'Payment pending': 'status-pending',
  Pending: 'status-pending',
  pending: 'status-pending',
  Shipped: 'status-shipping',
  'Payment failed': 'status-cancelled',
  Cancelled: 'status-cancelled',
  Failed: 'status-cancelled',
  failed: 'status-cancelled',
  Deleted: 'status-neutral',
}

export function StatusBadge({ label }: { label: string }) {
  return <span className={`status ${STATUS_CLASS[label] ?? 'status-neutral'}`}>{label}</span>
}
