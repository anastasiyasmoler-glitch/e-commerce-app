import { Fragment } from 'react'
import { PageHeader } from '@/components/ui/page-header'
import { RoleBadge } from '@/components/ui/role-badge'

type PermissionRow = {
  label: string
  customer: boolean
  analyst: boolean
  admin: boolean
}

const groups: { title: string; rows: PermissionRow[] }[] = [
  {
    title: 'Catalog',
    rows: [
      { label: 'Read products, categories, stores', customer: true, analyst: true, admin: true },
      { label: 'Create / edit / delete products, categories, stores', customer: false, analyst: false, admin: true },
      { label: 'Manage stock', customer: false, analyst: false, admin: true },
    ],
  },
  {
    title: 'Orders',
    rows: [
      { label: 'Create own order', customer: true, analyst: false, admin: false },
      { label: 'Read own orders', customer: true, analyst: false, admin: false },
      { label: 'Cancel own order (until shipped)', customer: true, analyst: false, admin: false },
      { label: 'Read all orders', customer: false, analyst: true, admin: true },
    ],
  },
  {
    title: 'Notifications',
    rows: [
      { label: 'Read notification history', customer: false, analyst: true, admin: true },
    ],
  },
  {
    title: 'Analytics',
    rows: [{ label: 'Read analytics', customer: false, analyst: true, admin: true }],
  },
  {
    title: 'AI Assistant',
    rows: [
      { label: 'Use assistant', customer: true, analyst: true, admin: true },
      { label: 'Reindex', customer: false, analyst: false, admin: true },
    ],
  },
  {
    title: 'Users',
    rows: [{ label: 'Assign Analyst role', customer: false, analyst: false, admin: true }],
  },
]

const attributeRules = [
  { title: 'Customers access only their own orders', code: 'order.user_id == user.id' },
  { title: 'Orders can be cancelled until shipped', code: 'order.status ∈ created … confirmed' },
]

export function AccessControlPage() {
  return (
    <>
      <PageHeader
        title="Access control"
        actions={<button className="btn btn-primary">Save changes</button>}
      />
      <div className="table-container">
        <table className="data-table acl-table">
          <thead>
            <tr>
              <th>Permission</th>
              <th>
                <RoleBadge role="customer" />
              </th>
              <th>
                <RoleBadge role="analyst" />
              </th>
              <th>
                <RoleBadge role="admin" />
              </th>
            </tr>
          </thead>
          <tbody>
            {groups.map((group) => (
              <Fragment key={group.title}>
                <tr className="group">
                  <td colSpan={4}>{group.title}</td>
                </tr>
                {group.rows.map((row) => (
                  <tr key={row.label}>
                    <td className="indent">{row.label}</td>
                    <td>
                      <input type="checkbox" defaultChecked={row.customer} aria-label={row.label} />
                    </td>
                    <td>
                      <input type="checkbox" defaultChecked={row.analyst} aria-label={row.label} />
                    </td>
                    <td>
                      <input type="checkbox" defaultChecked={row.admin} aria-label={row.label} />
                    </td>
                  </tr>
                ))}
              </Fragment>
            ))}
          </tbody>
        </table>
      </div>
      <div className="panel">
        <h2 className="panel-title">Attribute rules</h2>
        {attributeRules.map((rule) => (
          <div
            key={rule.title}
            className="spread"
            style={{ padding: '14px 0', borderBottom: '1px solid var(--line)' }}
          >
            <div>
              <b>{rule.title}</b>
              <div className="mono muted">{rule.code}</div>
            </div>
            <span className="switch">
              <input type="checkbox" defaultChecked aria-label={rule.title} />
              <span></span>
            </span>
          </div>
        ))}
      </div>
    </>
  )
}
