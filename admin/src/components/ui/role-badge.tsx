const ROLE_CLASS: Record<string, string> = {
  admin: 'role-admin',
  analyst: 'role-analyst',
  customer: 'role-customer',
}

export function RoleBadge({ role }: { role: string }) {
  const label = role.charAt(0).toUpperCase() + role.slice(1)
  return <span className={`role ${ROLE_CLASS[role] ?? 'role-customer'}`}>{label}</span>
}
