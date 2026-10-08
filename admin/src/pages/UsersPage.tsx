import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { http } from '../lib/http'

type AdminUserRow = {
  id: number
  name: string
  email: string
  phone: string | null
  roles: string[]
  is_analyst: boolean
}

export function UsersPage() {
  const queryClient = useQueryClient()
  const users = useQuery({
    queryKey: ['admin-users'],
    queryFn: async () => {
      const res = await http.get<{ data: AdminUserRow[] }>('/admin/users')
      return res.data.data
    },
  })

  const analyst = useMutation({
    mutationFn: async ({ id, next }: { id: number; next: boolean }) => {
      await http.patch(`/admin/users/${id}/analyst`, { analyst: next })
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin-users'] }),
  })

  return (
    <>
      <div className="admin-header">
        <h1 className="admin-title">Users</h1>
      </div>
      {users.isError ? <p className="auth-error">Could not load users.</p> : null}
      <div className="table-container">
        <table className="data-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Roles</th>
              <th>Analyst</th>
            </tr>
          </thead>
          <tbody>
            {(users.data ?? []).map((row) => {
              const locked = row.roles.includes('admin')
              return (
                <tr key={row.id}>
                  <td>{row.name}</td>
                  <td>{row.email}</td>
                  <td>{row.roles.join(', ')}</td>
                  <td>
                    <label className="role-toggle">
                      <input
                        type="checkbox"
                        checked={row.is_analyst}
                        disabled={locked || analyst.isPending}
                        onChange={(e) =>
                          analyst.mutate({ id: row.id, next: e.target.checked })
                        }
                      />
                      {locked ? 'Locked' : 'Assign analyst'}
                    </label>
                  </td>
                </tr>
              )
            })}
          </tbody>
        </table>
      </div>
    </>
  )
}
