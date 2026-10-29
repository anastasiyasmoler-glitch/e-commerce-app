import { http } from './http'

export type OrderApiItem = {
  product_id: number
  store_id: number
  product_name: string
  sku: string | null
  price_cents: number
  quantity: number
}

export type OrderApiRecord = {
  id: number
  user_id: number
  status: string
  total_cents: number
  currency: string
  shipping_address: Record<string, unknown> | null
  items: OrderApiItem[]
  created_at: string | null
}

export type OrderListMeta = {
  current_page: number
  per_page: number
  total: number
  last_page: number
}

export type OrderListResponse = {
  data: OrderApiRecord[]
  meta: OrderListMeta
}

export const orderKeys = {
  all: ['orders'] as const,
  list: (page: number) => ['orders', 'list', page] as const,
  detail: (id: number) => ['orders', 'detail', id] as const,
}

export async function listOrders(page = 1, perPage = 20): Promise<OrderListResponse> {
  const { data } = await http.get<OrderListResponse>('/api/orders', {
    params: { page, per_page: perPage },
  })

  return data
}

export async function getOrder(id: number): Promise<OrderApiRecord> {
  const { data } = await http.get<{ data: OrderApiRecord }>(`/api/orders/${id}`)
  return data.data
}

export async function createOrder(
  items: { product_id: number; quantity: number }[],
  idempotencyKey: string,
  shippingAddress?: Record<string, unknown>,
): Promise<OrderApiRecord> {
  const { data } = await http.post<{ data: OrderApiRecord }>(
    '/api/orders',
    {
      items,
      shipping_address: shippingAddress,
    },
    {
      headers: {
        'Idempotency-Key': idempotencyKey,
      },
    },
  )

  return data.data
}

export async function cancelOrder(id: number, reason?: string): Promise<OrderApiRecord> {
  const { data } = await http.post<{ data: OrderApiRecord }>(`/api/orders/${id}/cancel`, {
    reason,
  })

  return data.data
}
