import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { PenLine, Search } from 'lucide-react'
import { useState } from 'react'
import { adminKeys, fetchUsers, setUserRole } from '@/api/adminApi'
import { apiErrorMessage } from '@/api/http'
import { Notice } from '@/components/ui/notice'
import { PageHeader } from '@/components/ui/page-header'
import { RoleBadge } from '@/components/ui/role-badge'
import { Skeleton } from '@/components/ui/skeleton'
import { StatusBadge } from '@/components/ui/status-badge'

export function UsersPage() {
  const queryClient = useQueryClient()
  const [search, setSearch] = useState('')
  const [role, setRole] = useState('All roles')

  const users = useQuery({ queryKey: adminKeys.users, queryFn: fetchUsers })

  const analyst = useMutation({
    mutationFn: ({ id, next }: { id: number; next: boolean }) =>
      setUserRole(id, next ? 'analyst' : 'customer'),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: adminKeys.users }),
  })

  const rows = (users.data ?? []).filter((user) => {
    const matchesSearch =
      user.name.toLowerCase().includes(search.toLowerCase()) ||
      user.email.toLowerCase().includes(search.toLowerCase())
    const matchesRole = role === 'All roles' || user.role === role.toLowerCase()
    return matchesSearch && matchesRole
  })

  return (
    <>
      <PageHeader title="Users" />
      <Notice>
        You can assign the <b>Analyst</b> role. <b>Admin</b> accounts are created separately and
        cannot be assigned here.
      </Notice>
      {users.isError ? <Notice tone="danger">{apiErrorMessage(users.error)}</Notice> : null}
      <div className="table-container">
        <div className="table-toolbar">
          <div className="input-icon grow">
            <Search size={18} aria-hidden />
            <input
              className="input"
              placeholder="Search users"
              aria-label="Search"
              value={search}
              onChange={(event) => setSearch(event.target.value)}
            />
          </div>
          <select
            className="select"
            aria-label="Role"
            value={role}
            onChange={(event) => setRole(event.target.value)}
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
            {users.isPending
              ? Array.from({ length: 6 }, (_, index) => (
                  <tr key={index}>
                    <td colSpan={5}>
                      <Skeleton className="h-8 w-full" />
                    </td>
                  </tr>
                ))
              : null}
            {!users.isPending && !users.isError && rows.length === 0 ? (
              <tr>
                <td colSpan={5} className="muted">
                  No users found.
                </td>
              </tr>
            ) : null}
            {rows.map((user) => (
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
                      disabled={analyst.isPending}
                      onClick={() =>
                        analyst.mutate({ id: user.id, next: user.role !== 'analyst' })
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
