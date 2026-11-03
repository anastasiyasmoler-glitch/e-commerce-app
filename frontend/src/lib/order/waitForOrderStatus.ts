import { getOrder, type OrderRecord } from '@/api/orderApi'

const TERMINAL_FAILURE = new Set(['cancelled', 'payment_failed'])

export async function waitForOrderStatus(
  orderId: number,
  targetStatuses: string[],
  options?: { maxAttempts?: number; intervalMs?: number },
): Promise<OrderRecord> {
  const maxAttempts = options?.maxAttempts ?? 45
  const intervalMs = options?.intervalMs ?? 1000

  for (let attempt = 0; attempt < maxAttempts; attempt += 1) {
    const order = await getOrder(orderId)

    if (targetStatuses.includes(order.status)) {
      return order
    }

    if (TERMINAL_FAILURE.has(order.status)) {
      return order
    }

    await new Promise((resolve) => {
      setTimeout(resolve, intervalMs)
    })
  }

  throw new Error('Timed out waiting for the order to reach payment.')
}
