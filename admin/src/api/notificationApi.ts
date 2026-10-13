import axios, { isAxiosError } from 'axios'
import { refreshOnce } from './authApi'
import { getAccessToken } from './http'

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

const notificationHttp = axios.create({
  baseURL: (import.meta.env.VITE_NOTIFICATION_URL as string | undefined) || '',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

notificationHttp.interceptors.request.use((config) => {
  const token = getAccessToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

notificationHttp.interceptors.response.use(
  (response) => response,
  async (error: unknown) => {
    if (!isAxiosError(error) || !error.config) {
      return Promise.reject(error)
    }

    const original = error.config as typeof error.config & { _retried?: boolean }

    if (error.response?.status !== 401 || original._retried) {
      return Promise.reject(error)
    }

    original._retried = true

    try {
      await refreshOnce()
    } catch {
      return Promise.reject(error)
    }

    const token = getAccessToken()
    if (token) {
      original.headers.Authorization = `Bearer ${token}`
    }

    return notificationHttp(original)
  },
)

export async function listNotifications(): Promise<NotificationLog[]> {
  const { data } = await notificationHttp.get<{ data: NotificationLog[] }>('/api/notifications')
  return data.data
}

export async function getNotification(id: string): Promise<NotificationLog> {
  const { data } = await notificationHttp.get<{ data: NotificationLog }>(`/api/notifications/${id}`)
  return data.data
}
