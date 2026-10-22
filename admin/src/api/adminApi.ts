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

const delay = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms))

const users: AdminUser[] = [
  { id: 1, name: 'John Admin', email: 'john@example.com', role: 'admin', joined: 'Sep 15, 2026', active: true },
  { id: 2, name: 'Sara Analyst', email: 'sara@example.com', role: 'analyst', joined: 'Sep 20, 2026', active: true },
  { id: 3, name: 'Anna Smith', email: 'anna@example.com', role: 'customer', joined: 'Oct 1, 2026', active: true },
  { id: 4, name: 'Mike Brown', email: 'mike@example.com', role: 'customer', joined: 'Oct 5, 2026', active: true },
  { id: 5, name: 'Old Account', email: 'old@example.com', role: 'customer', joined: 'Aug 2, 2026', active: false },
]

const orders: AdminOrder[] = [
  { id: '#DOM-1042', customer: 'Anna Smith', date: 'Oct 8, 2026', total: '$844.00', status: 'Paid' },
  { id: '#DOM-1041', customer: 'John Doe', date: 'Oct 8, 2026', total: '$48.00', status: 'Confirmed' },
  { id: '#DOM-1040', customer: 'Maria Garcia', date: 'Oct 7, 2026', total: '$892.00', status: 'Payment pending' },
  { id: '#DOM-1039', customer: 'Robert Chen', date: 'Oct 7, 2026', total: '$199.00', status: 'Payment failed' },
  { id: '#DOM-1038', customer: 'Sarah Johnson', date: 'Oct 6, 2026', total: '$385.00', status: 'Shipped' },
  { id: '#DOM-1037', customer: 'Tom Brown', date: 'Oct 5, 2026', total: '$152.00', status: 'Cancelled' },
  { id: '#DOM-1036', customer: 'Eva Green', date: 'Oct 4, 2026', total: '$1,240.00', status: 'Completed' },
]

export async function fetchUsers(): Promise<AdminUser[]> {
  await delay(150)
  return users.map((user) => ({ ...user }))
}

export async function setUserRole(id: number, role: 'analyst' | 'customer'): Promise<void> {
  await delay(150)
  const user = users.find((item) => item.id === id)
  if (user) {
    user.role = role
  }
}

export async function fetchOrders(): Promise<AdminOrder[]> {
  await delay(150)
  return orders
}

