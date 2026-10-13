import { ExternalLink, LogOut } from 'lucide-react'
import { Link, useNavigate } from 'react-router-dom'
import { useLogout } from '@/hooks/useLogout'
import { paths } from '@/paths'

const storeUrl = () =>
  (import.meta.env.VITE_STORE_URL as string | undefined) ?? 'http://localhost:5173'

export function AdminBar() {
  const logout = useLogout()
  const navigate = useNavigate()

  return (
    <div className="admin-bar">
      <div className="container">
        <Link to={paths.analytics} className="logo">
          Domo<i>.</i>
        </Link>
        <span className="tag">Admin</span>
        <nav>
          <a href={storeUrl()}>
            <ExternalLink size={16} aria-hidden />
            Back to store
          </a>
          <button
            type="button"
            onClick={() =>
              logout.mutate(undefined, { onSettled: () => navigate(paths.login, { replace: true }) })
            }
          >
            <LogOut size={16} aria-hidden />
            Sign out
          </button>
        </nav>
      </div>
    </div>
  )
}
