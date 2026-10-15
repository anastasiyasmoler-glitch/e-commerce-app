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

export const notificationKeys = {
  all: ['notifications'] as const,
  list: ['notifications', 'list'] as const,
  detail: (id: string) => ['notifications', 'detail', id] as const,
}

export async function listNotifications(): Promise<NotificationLog[]> {
  const { data } = await http.get<{ data: NotificationLog[] }>('/api/notifications')
  return data.data
}

export async function getNotification(id: string): Promise<NotificationLog> {
  const { data } = await http.get<{ data: NotificationLog }>(`/api/notifications/${id}`)
  return data.data
}
