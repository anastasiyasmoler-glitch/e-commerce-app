import { http } from '@/api/http'
import { listOrders } from '@/api/orderApi'
import { mapOrderRow, type AdminOrderRow } from '@/lib/order/formatOrderRow'

export type AdminUser = {
  id: number
  name: string
  email: string
  role: 'admin' | 'analyst' | 'customer'
  joined: string
  active: boolean
}

export type AdminOrder = AdminOrderRow

export const adminKeys = {
  users: ['admin', 'users'] as const,
  orders: ['admin', 'orders'] as const,
}

type AuthAdminUserRow = {
  id: number
  name: string
  email: string
  phone: string | null
  roles: string[]
  is_analyst: boolean
  created_at: string | null
}

function primaryRole(roles: string[]): AdminUser['role'] {
  if (roles.includes('admin')) {
    return 'admin'
  }
  if (roles.includes('analyst')) {
    return 'analyst'
  }

  return 'customer'
}

function formatJoined(iso: string | null): string {
  if (!iso) {
    return '—'
  }

  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(new Date(iso))
}

export async function fetchUsers(): Promise<AdminUser[]> {
  const { data } = await http.get<{ data: AuthAdminUserRow[] }>('/admin/users')

  return data.data.map((row) => ({
    id: row.id,
    name: row.name,
    email: row.email,
    role: primaryRole(row.roles),
    joined: formatJoined(row.created_at),
    active: true,
  }))
}

export async function setUserAnalyst(id: number, analyst: boolean): Promise<void> {
  await http.patch(`/admin/users/${id}/analyst`, { analyst })
}

export async function fetchOrders(page = 1): Promise<AdminOrder[]> {
  const response = await listOrders(page, 50)
  return response.data.map(mapOrderRow)
}
