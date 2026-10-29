import { http } from '@/api/http'

export type AdminUser = {
  id: number
  name: string
  email: string
  role: 'admin' | 'analyst' | 'customer'
  joined: string
  active: boolean
}

export type AdminOrder = {
  id: string
  customer: string
  date: string
  total: string
  status: 'Paid' | 'Confirmed' | 'Payment pending' | 'Payment failed' | 'Shipped' | 'Cancelled' | 'Completed'
}

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

const delay = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms))

const orders: AdminOrder[] = [
  { id: '#DOM-1042', customer: 'Anna Smith', date: 'Oct 8, 2026', total: '$844.00', status: 'Paid' },
  { id: '#DOM-1041', customer: 'John Doe', date: 'Oct 8, 2026', total: '$48.00', status: 'Confirmed' },
  { id: '#DOM-1040', customer: 'Maria Garcia', date: 'Oct 7, 2026', total: '$892.00', status: 'Payment pending' },
  { id: '#DOM-1039', customer: 'Robert Chen', date: 'Oct 7, 2026', total: '$199.00', status: 'Payment failed' },
  { id: '#DOM-1038', customer: 'Sarah Johnson', date: 'Oct 6, 2026', total: '$385.00', status: 'Shipped' },
  { id: '#DOM-1037', customer: 'Tom Brown', date: 'Oct 5, 2026', total: '$152.00', status: 'Cancelled' },
  { id: '#DOM-1036', customer: 'Eva Green', date: 'Oct 4, 2026', total: '$1,240.00', status: 'Completed' },
]

export async function fetchOrders(): Promise<AdminOrder[]> {
  await delay(150)
  return orders
}
