import { useMemo, useState } from 'react'
import { useSetUserRole, useUsers } from '@/hooks/useUsers'

export function useUsersPage() {
  const [search, setSearch] = useState('')
  const [role, setRole] = useState('All roles')
  const users = useUsers()
  const analyst = useSetUserRole()

  const rows = useMemo(
    () =>
      (users.data ?? []).filter((user) => {
        const matchesSearch =
          user.name.toLowerCase().includes(search.toLowerCase()) ||
          user.email.toLowerCase().includes(search.toLowerCase())
        const matchesRole = role === 'All roles' || user.role === role.toLowerCase()
        return matchesSearch && matchesRole
      }),
    [users.data, search, role],
  )

  return {
    search,
    setSearch,
    role,
    setRole,
    users,
    analyst,
    rows,
  }
}
