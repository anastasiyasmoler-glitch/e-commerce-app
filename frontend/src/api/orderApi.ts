import { http } from './http'

export type OrderItem = {
  product_id: number
  store_id: number
  product_name: string
  sku: string | null
  price_cents: number
  quantity: number
}

export type OrderRecord = {
  id: number
  user_id: number
  status: string
  total_cents: number
  currency: string
  shipping_address: Record<string, unknown> | null
  items: OrderItem[]
  created_at: string | null
}

export type OrderListMeta = {
  current_page: number
  per_page: number
  total: number
  last_page: number
}

export const orderKeys = {
  all: ['orders'] as const,
  list: (page: number) => ['orders', 'list', page] as const,
  detail: (id: number) => ['orders', 'detail', id] as const,
}

export async function listOrders(page = 1, perPage = 20): Promise<{
  data: OrderRecord[]
  meta: OrderListMeta
}> {
  const { data } = await http.get<{ data: OrderRecord[]; meta: OrderListMeta }>('/api/orders', {
    params: { page, per_page: perPage },
  })

  return data
}

export async function getOrder(id: number): Promise<OrderRecord> {
  const { data } = await http.get<{ data: OrderRecord }>(`/api/orders/${id}`)
  return data.data
}

export async function createOrder(
  items: { product_id: number; quantity: number }[],
  idempotencyKey: string,
): Promise<OrderRecord> {
  const { data } = await http.post<{ data: OrderRecord }>(
    '/api/orders',
    { items },
    { headers: { 'Idempotency-Key': idempotencyKey } },
  )

  return data.data
}

export async function cancelOrder(id: number): Promise<OrderRecord> {
  const { data } = await http.post<{ data: OrderRecord }>(`/api/orders/${id}/cancel`, {})
  return data.data
}
