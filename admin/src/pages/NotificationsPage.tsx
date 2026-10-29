import { Eye } from 'lucide-react'
import { apiErrorMessage } from '@/api/http'
import { formatNotificationWhen } from '@/lib/notifications/filters'
import { useNotificationsPage } from '@/hooks/useNotificationsPage'
import { PageHeader } from '@/components/ui/page-header'
import { Skeleton } from '@/components/ui/skeleton'
import { StatusBadge } from '@/components/ui/status-badge'

export function NotificationsPage() {
  const page = useNotificationsPage()

  return (
    <>
      <PageHeader title="Notification history" />
      <div className="table-container">
        <div className="table-toolbar">
          <div className="input-icon grow">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="18"
              height="18"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
              aria-hidden="true"
            >
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.35-4.35" />
            </svg>
            <input
              className="input"
              placeholder="Recipient"
              aria-label="Recipient"
              value={page.recipient}
              onChange={(event) => page.setRecipient(event.target.value)}
            />
          </div>
          <select
            className="select"
            aria-label="Status"
            value={page.status}
            onChange={(event) => page.setStatus(event.target.value)}
          >
            <option value="all">All statuses</option>
            <option value="pending">Pending</option>
            <option value="sent">Sent</option>
            <option value="failed">Failed</option>
          </select>
          <select
            className="select"
            aria-label="Channel"
            value={page.channel}
            onChange={(event) => page.setChannel(event.target.value)}
          >
            <option value="all">All channels</option>
            <option value="email">Email</option>
          </select>
          <input
            className="input"
            type="date"
            aria-label="From"
            value={page.from}
            onChange={(event) => page.setFrom(event.target.value)}
          />
          <input
            className="input"
            type="date"
            aria-label="To"
            value={page.to}
            onChange={(event) => page.setTo(event.target.value)}
          />
        </div>
        {page.list.isError ? (
          <p className="muted" style={{ padding: 'var(--space-6)' }}>
            {apiErrorMessage(page.list.error)}
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
            {page.list.isPending
              ? Array.from({ length: 5 }, (_, index) => (
                  <tr key={index}>
                    <td colSpan={7}>
                      <Skeleton className="h-6 w-full" />
                    </td>
                  </tr>
                ))
              : null}
            {!page.list.isPending && page.rows.length === 0 ? (
              <tr>
                <td colSpan={7} className="muted">
                  No notifications found.
                </td>
              </tr>
            ) : null}
            {page.rows.map((item) => (
              <tr key={item.id}>
                <td className="mono">{item.event}</td>
                <td>{item.channel}</td>
                <td>{item.recipient}</td>
                <td>
                  <StatusBadge label={page.statusLabels[item.status]} />
                </td>
                <td>{item.attempts}</td>
                <td className="muted">{formatNotificationWhen(item.created_at)}</td>
                <td>
                  <button
                    type="button"
                    className="table-action"
                    onClick={() => page.setSelectedId(item.id)}
                  >
                    <Eye size={14} aria-hidden />
                    Details
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      {page.selectedId ? (
        <div className="panel">
          {page.detail.isPending ? <Skeleton className="h-24 w-full" /> : null}
          {page.detail.isError ? <p className="muted">{apiErrorMessage(page.detail.error)}</p> : null}
          {page.selected ? (
            <>
              <div className="spread" style={{ marginBottom: 'var(--space-4)' }}>
                <h2 className="panel-title" style={{ margin: 0 }}>
                  Notification details
                </h2>
                <div className="spread" style={{ gap: 8, width: 'auto' }}>
                  <StatusBadge label={page.statusLabel ?? page.selected.status} />
                  {page.selected.is_dlq ? <span className="muted">Moved to DLQ</span> : null}
                  {page.selected.is_dlq ? (
                    <button
                      type="button"
                      className="table-action"
                      disabled={page.replay.isPending}
                      onClick={() => {
                        if (page.selectedId) {
                          page.replay.mutate(page.selectedId)
                        }
                      }}
                    >
                      {page.replay.isPending ? 'Replaying…' : 'Replay'}
                    </button>
                  ) : null}
                </div>
              </div>
              <div className="grid-2" style={{ marginBottom: 'var(--space-4)' }}>
                <div>
                  <div className="muted" style={{ fontSize: 'var(--text-sm)' }}>
                    Event
                  </div>
                  <b className="mono">{page.selected.event}</b>
                </div>
                <div>
                  <div className="muted" style={{ fontSize: 'var(--text-sm)' }}>
                    Attempts
                  </div>
                  <b>{page.selected.attempts}</b>
                </div>
              </div>
              {page.replay.isError ? (
                <p className="muted">{apiErrorMessage(page.replay.error)}</p>
              ) : null}
              <pre className="code mono">
                {JSON.stringify(
                  {
                    id: page.selected.id,
                    recipient: page.selected.recipient,
                    channel: page.selected.channel,
                    status: page.selected.status,
                    attempts: page.selected.attempts,
                    is_dlq: page.selected.is_dlq,
                    error_message: page.selected.error_message,
                    created_at: page.selected.created_at,
                    sent_at: page.selected.sent_at,
                    payload: page.selected.payload,
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
