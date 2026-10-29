import { useState } from 'react'
import { useNotification, useNotifications, useReplayNotification } from '@/hooks/useNotifications'
import { NOTIFICATION_STATUS_LABEL } from '@/lib/notifications/filters'

export function useNotificationsPage() {
  const [recipient, setRecipient] = useState('')
  const [status, setStatus] = useState('all')
  const [channel, setChannel] = useState('all')
  const [from, setFrom] = useState('')
  const [to, setTo] = useState('')
  const [selectedId, setSelectedId] = useState<string | null>(null)

  const filters = {
    recipient,
    status,
    channel,
    from,
    to,
  }

  const list = useNotifications(filters)
  const detail = useNotification(selectedId)
  const replay = useReplayNotification()

  const rows = list.data ?? []
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
    replay,
  }
}
