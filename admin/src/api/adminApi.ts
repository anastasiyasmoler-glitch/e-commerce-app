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
  analytics: ['admin', 'analytics'] as const,
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

export type AnalyticsSummary = {
  kpis: { label: string; value: string; delta: string; up: boolean }[]
  userGrowth: { points: string; area: string }
  funnel: { label: string; value: string; width: number; bad?: boolean }[]
  revenue: { day: string; value: string; height: number }[]
  popularSearches: { term: string; count: string }[]
  emptySearches: { term: string; count: string }[]
}

const analytics: AnalyticsSummary = {
  kpis: [
    { label: 'Revenue', value: '$48.5k', delta: '12.5% vs previous period', up: true },
    { label: 'Orders', value: '1,248', delta: '8.2% vs previous period', up: true },
    { label: 'New users', value: '384', delta: '15.3% vs previous period', up: true },
    { label: 'Average order value', value: '$38.90', delta: '2.4% vs previous period', up: false },
  ],
  userGrowth: {
    area: '0,200 0,164 51,153 102,144 153,148 204,128 255,117 305,106 356,96 407,81 458,70 509,54 560,29 560,200',
    points: '0,164 51,153 102,144 153,148 204,128 255,117 305,106 356,96 407,81 458,70 509,54 560,29',
  },
  funnel: [
    { label: 'Created', value: '1,248 · 100%', width: 100 },
    { label: 'Paid', value: '1,061 · 85%', width: 85 },
    { label: 'Completed', value: '984 · 79%', width: 79 },
    { label: 'Cancelled', value: '187 · 15%', width: 15, bad: true },
  ],
  revenue: [
    { day: 'Mon', value: '$6.2k', height: 51 },
    { day: 'Tue', value: '$5.1k', height: 42 },
    { day: 'Wed', value: '$7.4k', height: 61 },
    { day: 'Thu', value: '$5.8k', height: 48 },
    { day: 'Fri', value: '$8.9k', height: 74 },
    { day: 'Sat', value: '$9.6k', height: 80 },
    { day: 'Sun', value: '$6.9k', height: 57 },
  ],
  popularSearches: [
    { term: 'sofa', count: '1,820' },
    { term: 'pink sofa', count: '940' },
    { term: 'floor lamp', count: '712' },
    { term: 'dining table', count: '655' },
    { term: 'bedding set', count: '540' },
  ],
  emptySearches: [
    { term: 'green velvet armchair', count: '48 searches' },
    { term: 'sofa 300 cm', count: '37 searches' },
    { term: 'vintage sideboard', count: '29 searches' },
    { term: 'outdoor sofa', count: '22 searches' },
  ],
}

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

export async function fetchAnalytics(): Promise<AnalyticsSummary> {
  await delay(150)
  return analytics
}
