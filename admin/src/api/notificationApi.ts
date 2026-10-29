import { http } from './http'

export type NotificationStatus = 'pending' | 'sent' | 'failed'

export type NotificationLog = {
  id: string
  event: string
  channel: string
  recipient: string
  payload: Record<string, unknown> | null
  status: NotificationStatus
  attempts: number
  error_message: string | null
  is_dlq: boolean
  sent_at: string | null
  created_at: string | null
}

export type NotificationListFilters = {
  recipient?: string
  status?: string
  channel?: string
  from?: string
  to?: string
}

export const notificationKeys = {
  all: ['notifications'] as const,
  list: (filters: NotificationListFilters) => ['notifications', 'list', filters] as const,
  detail: (id: string) => ['notifications', 'detail', id] as const,
}

export async function listNotifications(
  filters: NotificationListFilters = {},
): Promise<NotificationLog[]> {
  const params = new URLSearchParams()
  if (filters.recipient?.trim()) {
    params.set('recipient', filters.recipient.trim())
  }
  if (filters.status && filters.status !== 'all') {
    params.set('status', filters.status)
  }
  if (filters.channel && filters.channel !== 'all') {
    params.set('channel', filters.channel)
  }
  if (filters.from) {
    params.set('from', filters.from)
  }
  if (filters.to) {
    params.set('to', filters.to)
  }

  const query = params.toString()
  const url = query ? `/api/notifications?${query}` : '/api/notifications'
  const { data } = await http.get<{ data: NotificationLog[] }>(url)

  return data.data
}

export async function getNotification(id: string): Promise<NotificationLog> {
  const { data } = await http.get<{ data: NotificationLog }>(`/api/notifications/${id}`)
  return data.data
}
