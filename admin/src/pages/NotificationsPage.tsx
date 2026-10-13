import { Eye } from 'lucide-react'
import { useMemo, useState } from 'react'
import { apiErrorMessage } from '@/api/http'
import type { NotificationLog, NotificationStatus } from '@/api/notificationApi'
import { useNotification, useNotifications } from '@/hooks/useNotifications'
import { PageHeader } from '@/components/ui/page-header'
import { Skeleton } from '@/components/ui/skeleton'
import { StatusBadge } from '@/components/ui/status-badge'

const STATUS_LABEL: Record<NotificationStatus, string> = {
  pending: 'Pending',
  sent: 'Sent',
  failed: 'Failed',
}

function formatWhen(value: string | null): string {
  if (!value) {
    return '—'
  }

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return value
  }

  return date.toLocaleString()
}

function matchesFilters(
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

export function NotificationsPage() {
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
      (list.data ?? []).filter((item) => matchesFilters(item, recipient, status, channel, from, to)),
    [list.data, recipient, status, channel, from, to],
  )

  const selected = detail.data
  const statusLabel = selected ? STATUS_LABEL[selected.status] : null

  return (
    <>
      <PageHeader title="Notification history" />
      <div className="table-container">
        <div className="table-toolbar">
          <div className="input-icon grow">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.35-4.35" />
            </svg>
            <input
              className="input"
              placeholder="Recipient"
              aria-label="Recipient"
              value={recipient}
              onChange={(event) => setRecipient(event.target.value)}
            />
          </div>
          <select className="select" aria-label="Status" value={status} onChange={(event) => setStatus(event.target.value)}>
            <option value="all">All statuses</option>
            <option value="pending">Pending</option>
            <option value="sent">Sent</option>
            <option value="failed">Failed</option>
          </select>
          <select className="select" aria-label="Channel" value={channel} onChange={(event) => setChannel(event.target.value)}>
            <option value="all">All channels</option>
            <option value="email">Email</option>
          </select>
          <input className="input" type="date" aria-label="From" value={from} onChange={(event) => setFrom(event.target.value)} />
          <input className="input" type="date" aria-label="To" value={to} onChange={(event) => setTo(event.target.value)} />
        </div>
        {list.isError ? (
          <p className="muted" style={{ padding: 'var(--space-6)' }}>
            {apiErrorMessage(list.error)}
          </p>
        ) : null}
        <table className="data-table">
          <thead>
            <tr>
              <th>Event</th>
              <th>Channel</th>
              <th>Recipient</th>
              <th>Status</th>
              <th>Attempts</th>
              <th>Created</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {list.isPending
              ? Array.from({ length: 5 }, (_, index) => (
                  <tr key={index}>
                    <td colSpan={7}>
                      <Skeleton className="h-6 w-full" />
                    </td>
                  </tr>
                ))
              : null}
            {!list.isPending && rows.length === 0 ? (
              <tr>
                <td colSpan={7} className="muted">
                  No notifications found.
                </td>
              </tr>
            ) : null}
            {rows.map((item) => (
              <tr key={item.id}>
                <td className="mono">{item.event}</td>
                <td>{item.channel}</td>
                <td>{item.recipient}</td>
                <td>
                  <StatusBadge label={STATUS_LABEL[item.status]} />
                </td>
                <td>{item.attempts}</td>
                <td className="muted">{formatWhen(item.created_at)}</td>
                <td>
                  <button type="button" className="table-action" onClick={() => setSelectedId(item.id)}>
                    <Eye size={14} aria-hidden />
                    Details
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      {selectedId ? (
        <div className="panel">
          {detail.isPending ? <Skeleton className="h-24 w-full" /> : null}
          {detail.isError ? <p className="muted">{apiErrorMessage(detail.error)}</p> : null}
          {selected ? (
            <>
              <div className="spread" style={{ marginBottom: 'var(--space-4)' }}>
                <h2 className="panel-title" style={{ margin: 0 }}>
                  Notification details
                </h2>
                <div className="spread" style={{ gap: 8, width: 'auto' }}>
                  <StatusBadge label={statusLabel ?? selected.status} />
                  {selected.is_dlq ? <span className="muted">Moved to DLQ</span> : null}
                </div>
              </div>
              <div className="grid-2" style={{ marginBottom: 'var(--space-4)' }}>
                <div>
                  <div className="muted" style={{ fontSize: 'var(--text-sm)' }}>
                    Event
                  </div>
                  <b className="mono">{selected.event}</b>
                </div>
                <div>
                  <div className="muted" style={{ fontSize: 'var(--text-sm)' }}>
                    Attempts
                  </div>
                  <b>{selected.attempts}</b>
                </div>
              </div>
              <pre className="code mono">
                {JSON.stringify(
                  {
                    id: selected.id,
                    recipient: selected.recipient,
                    channel: selected.channel,
                    status: selected.status,
                    attempts: selected.attempts,
                    is_dlq: selected.is_dlq,
                    error_message: selected.error_message,
                    created_at: selected.created_at,
                    sent_at: selected.sent_at,
                    payload: selected.payload,
                  },
                  null,
                  2,
                )}
              </pre>
            </>
          ) : null}
        </div>
      ) : null}
    </>
  )
}
