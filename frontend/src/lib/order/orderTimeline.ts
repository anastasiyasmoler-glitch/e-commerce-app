export type OrderTimelineStepId =
  | 'created'
  | 'stock_reserved'
  | 'payment_pending'
  | 'paid'
  | 'confirmed'
  | 'shipped'
  | 'completed'

export type OrderTimelineStep = {
  id: OrderTimelineStepId
  title: string
  description: string
}

export const ORDER_TIMELINE_STEPS: OrderTimelineStep[] = [
  { id: 'created', title: 'Order created', description: 'We received your order' },
  { id: 'stock_reserved', title: 'Stock reserved', description: 'Items are reserved for you' },
  { id: 'payment_pending', title: 'Payment pending', description: 'Waiting for payment confirmation' },
  { id: 'paid', title: 'Paid', description: 'Payment confirmed by Stripe' },
  { id: 'confirmed', title: 'Confirmed', description: 'Confirmation email sent' },
  { id: 'shipped', title: 'Shipped', description: 'Your order is on its way' },
  { id: 'completed', title: 'Completed', description: 'Order delivered' },
]

const STATUS_INDEX: Record<string, number> = {
  created: 0,
  stock_reserved: 1,
  payment_pending: 2,
  paid: 3,
  confirmed: 4,
  shipped: 5,
  completed: 6,
}

export type TimelineStepState = 'done' | 'current' | 'todo' | 'failed'

export function orderTimelineProgress(status: string): {
  steps: Array<OrderTimelineStep & { state: TimelineStepState }>
  isFailure: boolean
} {
  if (status === 'payment_failed' || status === 'cancelled') {
    return {
      isFailure: true,
      steps: ORDER_TIMELINE_STEPS.map((step, index) => ({
        ...step,
        state: index === 0 ? 'done' : index === 1 ? 'failed' : 'todo',
      })),
    }
  }

  const index = STATUS_INDEX[status] ?? 0

  return {
    isFailure: false,
    steps: ORDER_TIMELINE_STEPS.map((step, stepIndex) => {
      if (stepIndex < index) {
        return { ...step, state: 'done' as const }
      }
      if (stepIndex === index) {
        return { ...step, state: 'current' as const }
      }
      return { ...step, state: 'todo' as const }
    }),
  }
}

export function orderConfirmationBanner(
  status: string,
  options?: { returnedFromStripe?: boolean },
): {
  tone: 'ok' | 'pending' | 'bad'
  title: string
  body: string
} {
  if (status === 'payment_failed') {
    return {
      tone: 'bad',
      title: 'Payment could not be completed',
      body: 'Try placing the order again or use a different card.',
    }
  }

  if (status === 'cancelled') {
    return {
      tone: 'bad',
      title: 'This order was cancelled',
      body: 'Reserved stock has been released.',
    }
  }

  if (status === 'confirmed' || status === 'shipped' || status === 'completed') {
    return {
      tone: 'ok',
      title: 'Thank you. Your order is confirmed',
      body: 'We sent a confirmation email with the details below.',
    }
  }

  if (status === 'paid') {
    return {
      tone: 'ok',
      title: 'Payment received',
      body: 'We are confirming your order. This usually takes a few seconds.',
    }
  }

  if (options?.returnedFromStripe && status === 'payment_pending') {
    return {
      tone: 'pending',
      title: 'You paid on Stripe — confirming',
      body: 'Stripe accepted the payment. This page updates when Order receives the webhook.',
    }
  }

  return {
    tone: 'pending' as const,
    title: 'Thank you. Your order is being processed',
    body: 'We will email you when it is confirmed.',
  }
}

export function canCancelOrder(status: string): boolean {
  return !['shipped', 'completed', 'cancelled', 'payment_failed'].includes(status)
}
