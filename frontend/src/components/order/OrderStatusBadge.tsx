import { orderStatusLabel } from '@/lib/order/orderStatus'

type OrderStatusBadgeProps = {
  status: string
}

export function OrderStatusBadge({ status }: OrderStatusBadgeProps) {
  return (
    <span className="inline-flex rounded-[6px] border border-line bg-subtle px-2 py-0.5 text-[12px] font-medium text-ink">
      {orderStatusLabel(status)}
    </span>
  )
}
