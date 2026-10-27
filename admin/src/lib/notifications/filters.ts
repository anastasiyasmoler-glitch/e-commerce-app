import type { NotificationLog } from '@/api/notificationApi'

export const NOTIFICATION_STATUS_LABEL: Record<
  'pending' | 'sent' | 'failed',
  string
> = {
  pending: 'Pending',
  sent: 'Sent',
  failed: 'Failed',
}

export function formatNotificationWhen(value: string | null): string {
  if (!value) {
    return '—'
  }

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return value
  }

  return date.toLocaleString()
}

export function matchesNotificationFilters(
  item: NotificationLog,
  recipient: string,
  status: string,
  channel: string,
  from: string,
  to: string,
): boolean {
  if (recipient && !item.recipient.toLowerCase().includes(recipient.toLowerCase())) {
    return false
  }

  if (status !== 'all' && item.status !== status) {
    return false
  }

  if (channel !== 'all' && item.channel !== channel) {
    return false
  }

  if (from || to) {
    const created = item.created_at ? new Date(item.created_at).getTime() : NaN
    if (Number.isNaN(created)) {
      return false
    }
    if (from && created < new Date(`${from}T00:00:00`).getTime()) {
      return false
    }
    if (to && created > new Date(`${to}T23:59:59`).getTime()) {
      return false
    }
  }

  return true
}
