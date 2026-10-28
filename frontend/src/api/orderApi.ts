import { http } from './http'

export const orderKeys = {
  all: ['orders'] as const,
  list: () => [...orderKeys.all, 'list'] as const,
  detail: (id: number) => [...orderKeys.all, 'detail', id] as const,
}

export type OrderItem = {
  product_id: number
  store_id: number
  product_name: string
  sku: string | null
  price_cents: number
  quantity: number
}

export type Order = {
  id: number
  user_id: number
  status: string
  total_cents: number
  currency: string
  shipping_address: Record<string, unknown> | null
  items: OrderItem[]
  created_at: string | null
}

type PaginatedOrders = {
  data: Order[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export async function fetchOrders(page = 1): Promise<PaginatedOrders> {
  const { data } = await http.get<PaginatedOrders>('/api/orders', { params: { page } })
  return data
}

export async function fetchOrder(orderId: number): Promise<Order> {
  const { data } = await http.get<{ data: Order }>(`/api/orders/${orderId}`)
  return data.data
}

export type CreateOrderPayload = {
  items: Array<{ product_id: number; quantity: number }>
  shipping_address?: Record<string, string>
  currency?: string
}

export async function createOrder(
  payload: CreateOrderPayload,
  idempotencyKey: string,
): Promise<Order> {
  const { data } = await http.post<{ data: Order }>('/api/orders', payload, {
    headers: { 'Idempotency-Key': idempotencyKey },
  })
  return data.data
}

export async function cancelOrder(orderId: number, reason?: string): Promise<Order> {
  const { data } = await http.post<{ data: Order }>(`/api/orders/${orderId}/cancel`, {
    reason: reason ?? '',
  })
  return data.data
}
