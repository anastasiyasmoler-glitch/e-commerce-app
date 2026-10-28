import { Badge } from '@/components/ui/badge'
import { orderStatusLabel } from '@/lib/order/orderStatus'

type OrderStatusBadgeProps = {
  status: string
}

export function OrderStatusBadge({ status }: OrderStatusBadgeProps) {
  const variant =
    status === 'cancelled' || status === 'payment_failed'
      ? 'destructive'
      : status === 'completed' || status === 'paid' || status === 'confirmed'
        ? 'secondary'
        : 'outline'

  return <Badge variant={variant}>{orderStatusLabel(status)}</Badge>
}
