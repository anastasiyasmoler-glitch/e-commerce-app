import { useMemo, useState } from 'react'
import { useNotification, useNotifications } from '@/hooks/useNotifications'
import {
  matchesNotificationFilters,
  NOTIFICATION_STATUS_LABEL,
} from '@/lib/notifications/filters'

export function useNotificationsPage() {
  const [recipient, setRecipient] = useState('')
  const [status, setStatus] = useState('all')
  const [channel, setChannel] = useState('all')
  const [from, setFrom] = useState('')
  const [to, setTo] = useState('')
  const [selectedId, setSelectedId] = useState<string | null>(null)

  const list = useNotifications()
  const detail = useNotification(selectedId)

  const rows = useMemo(
    () =>
      (list.data ?? []).filter((item) =>
        matchesNotificationFilters(item, recipient, status, channel, from, to),
      ),
    [list.data, recipient, status, channel, from, to],
  )

  const selected = detail.data
  const statusLabel = selected ? NOTIFICATION_STATUS_LABEL[selected.status] : null

  return {
    recipient,
    setRecipient,
    status,
    setStatus,
    channel,
    setChannel,
    from,
    setFrom,
    to,
    setTo,
    selectedId,
    setSelectedId,
    list,
    detail,
    rows,
    selected,
    statusLabel,
    statusLabels: NOTIFICATION_STATUS_LABEL,
  }
}
