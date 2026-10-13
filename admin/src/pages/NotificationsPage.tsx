import { useQuery } from '@tanstack/react-query'
import { Eye, Search } from 'lucide-react'
import { useState } from 'react'
import { adminKeys, fetchNotifications, type AdminNotification } from '@/api/adminApi'
import { PageHeader } from '@/components/ui/page-header'
import { StatusBadge } from '@/components/ui/status-badge'

const failedDetail = `{ "order_id": "DOM-1042", "recipient": "bad@mailbox.test", "channel": "email", "status": "failed", "attempts": 5,
  "created_at": "2026-10-08T09:14:02Z", "sent_at": null }`

export function NotificationsPage() {
  const [recipient, setRecipient] = useState('')
  const [status, setStatus] = useState('All statuses')
  const notifications = useQuery({ queryKey: adminKeys.notifications, queryFn: fetchNotifications })

  const rows = (notifications.data ?? []).filter((item: AdminNotification) => {
    const matchesRecipient = item.recipient.toLowerCase().includes(recipient.toLowerCase())
    const matchesStatus = status === 'All statuses' || item.status === status
    return matchesRecipient && matchesStatus
  })

  return (
    <>
      <PageHeader title="Notification history" />
      <div className="table-container">
        <div className="table-toolbar">
          <div className="input-icon grow">
            <Search size={18} aria-hidden />
            <input
              className="input"
              placeholder="Recipient"
              aria-label="Recipient"
              value={recipient}
              onChange={(event) => setRecipient(event.target.value)}
            />
          </div>
          <select
            className="select"
            aria-label="Status"
            value={status}
            onChange={(event) => setStatus(event.target.value)}
          >
            <option>All statuses</option>
            <option>Pending</option>
            <option>Sent</option>
            <option>Failed</option>
          </select>
          <select className="select" aria-label="Channel">
            <option>All channels</option>
            <option>Email</option>
          </select>
          <input className="input" type="date" aria-label="From" />
          <input className="input" type="date" aria-label="To" />
        </div>
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
            {rows.map((item) => (
              <tr key={item.id}>
                <td className="mono">{item.event}</td>
                <td>{item.channel}</td>
                <td>{item.recipient}</td>
                <td>
                  <StatusBadge label={item.status} />
                </td>
                <td>{item.attempts}</td>
                <td className="muted">{item.created}</td>
                <td>
                  <button type="button" className="table-action">
                    <Eye size={14} aria-hidden />
                    Details
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <div className="panel">
        <div className="spread" style={{ marginBottom: 'var(--space-4)' }}>
          <h2 className="panel-title" style={{ margin: 0 }}>
            Notification details
          </h2>
          <span className="status status-cancelled">Failed · moved to DLQ</span>
        </div>
        <div className="grid-2" style={{ marginBottom: 'var(--space-4)' }}>
          <div>
            <div className="muted" style={{ fontSize: 'var(--text-sm)' }}>
              Event
            </div>
            <b className="mono">order.paid</b>
          </div>
          <div>
            <div className="muted" style={{ fontSize: 'var(--text-sm)' }}>
              Attempts
            </div>
            <b>5</b>
          </div>
        </div>
        <pre className="code mono">{failedDetail}</pre>
      </div>
    </>
  )
}
