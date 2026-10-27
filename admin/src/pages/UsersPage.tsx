import { PenLine, Search } from 'lucide-react'
import { apiErrorMessage } from '@/api/http'
import { useUsersPage } from '@/hooks/useUsersPage'
import { Notice } from '@/components/ui/notice'
import { PageHeader } from '@/components/ui/page-header'
import { RoleBadge } from '@/components/ui/role-badge'
import { Skeleton } from '@/components/ui/skeleton'
import { StatusBadge } from '@/components/ui/status-badge'

export function UsersPage() {
  const usersPage = useUsersPage()

  return (
    <>
      <PageHeader title="Users" />
      <Notice>
        You can assign the <b>Analyst</b> role. <b>Admin</b> accounts are created separately and
        cannot be assigned here.
      </Notice>
      {usersPage.users.isError ? (
        <Notice tone="danger">{apiErrorMessage(usersPage.users.error)}</Notice>
      ) : null}
      <div className="table-container">
        <div className="table-toolbar">
          <div className="input-icon grow">
            <Search size={18} aria-hidden />
            <input
              className="input"
              placeholder="Search users"
              aria-label="Search"
              value={usersPage.search}
              onChange={(event) => usersPage.setSearch(event.target.value)}
            />
          </div>
          <select
            className="select"
            aria-label="Role"
            value={usersPage.role}
            onChange={(event) => usersPage.setRole(event.target.value)}
          >
            <option>All roles</option>
            <option>Customer</option>
            <option>Analyst</option>
            <option>Admin</option>
          </select>
        </div>
        <table className="data-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Role</th>
              <th>Joined</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {usersPage.users.isPending
              ? Array.from({ length: 6 }, (_, index) => (
                  <tr key={index}>
                    <td colSpan={5}>
                      <Skeleton className="h-8 w-full" />
                    </td>
                  </tr>
                ))
              : null}
            {!usersPage.users.isPending && !usersPage.users.isError && usersPage.rows.length === 0 ? (
              <tr>
                <td colSpan={5} className="muted">
                  No users found.
                </td>
              </tr>
            ) : null}
            {usersPage.rows.map((user) => (
              <tr key={user.id}>
                <td>
                  <div className="cell-product">
                    <div
                      className="store-avatar"
                      style={{
                        width: 36,
                        height: 36,
                        fontSize: 'var(--text-base)',
                        borderRadius: '50%',
                        background: 'var(--bg-subtle)',
                        color: 'var(--ink)',
                      }}
                    >
                      {user.name.charAt(0)}
                    </div>
                    <div>
                      <div>{user.name}</div>
                      <div className="cell-sub">{user.email}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <RoleBadge role={user.role} />
                </td>
                <td>{user.joined}</td>
                <td>
                  <StatusBadge label={user.active ? 'Active' : 'Deleted'} />
                </td>
                <td>
                  {user.role === 'admin' ? (
                    <span className="muted" style={{ fontSize: 'var(--text-sm)' }}>
                      Seeded account
                    </span>
                  ) : user.active ? (
                    <button
                      type="button"
                      className="table-action"
                      disabled={usersPage.analyst.isPending}
                      onClick={() =>
                        usersPage.analyst.mutate({ id: user.id, next: user.role !== 'analyst' })
                      }
                    >
                      <PenLine size={14} aria-hidden />
                      {user.role === 'analyst' ? 'Remove Analyst role' : 'Make Analyst'}
                    </button>
                  ) : null}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </>
  )
}
