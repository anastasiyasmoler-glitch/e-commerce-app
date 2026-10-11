import { ChevronRight, LogOut, Package, Sparkles, Trash2, User } from 'lucide-react'
import { type FormEvent } from 'react'
import { Link } from 'react-router-dom'
import { clearSession } from '../auth/session'

const fieldClass =
  'h-11 w-full rounded-sm border border-line-strong bg-surface px-4 text-[15px] outline-none focus:border-brand focus:shadow-[0_0_0_3px_var(--brand-glow)]'

type ProfilePageProps = {
  onSignedOut: () => void
}

export function ProfilePage({ onSignedOut }: ProfilePageProps) {
  function onSave(event: FormEvent) {
    event.preventDefault()
  }

  function onSignOut() {
    clearSession()
    onSignedOut()
  }

  return (
    <div className="mx-auto max-w-[1240px] px-6 py-8">
      <nav className="mb-4 flex items-center gap-1 text-[13px] text-muted" aria-label="Breadcrumb">
        <Link to="/" className="hover:text-ink">
          Home
        </Link>
        <ChevronRight size={14} />
        <span className="text-ink">Profile</span>
      </nav>
      <h1 className="font-display mb-8 text-[36px] font-medium">Profile</h1>
      <div className="grid gap-10 lg:grid-cols-[240px_1fr]">
        <nav className="flex flex-col gap-1 text-[14px] font-medium">
          <span className="flex items-center gap-2 rounded-sm bg-brand-soft px-3 py-2.5 text-brand">
            <User size={18} /> Profile
          </span>
          <span className="flex items-center gap-2 px-3 py-2.5 text-ink">
            <Package size={18} /> My orders
          </span>
          <span className="flex items-center gap-2 px-3 py-2.5 text-ink">
            <Sparkles size={18} /> AI Assistant
          </span>
          <Link to="/" onClick={onSignOut} className="flex items-center gap-2 px-3 py-2.5 text-ink">
            <LogOut size={18} /> Sign out
          </Link>
        </nav>
        <div className="space-y-6">
          <form className="rounded-lg border border-line bg-surface p-6" onSubmit={onSave}>
            <h2 className="text-[18px] font-semibold">Personal details</h2>
            <p className="mt-1 mb-5 text-[14px] text-muted">
              Role:{' '}
              <span className="rounded-full bg-brand-soft px-2 py-0.5 text-[12px] font-semibold text-brand">Customer</span>
            </p>
            <div className="mb-4 grid gap-4 sm:grid-cols-2">
              <div>
                <label className="mb-1.5 block text-[13px] font-semibold">Full name</label>
                <input className={fieldClass} defaultValue="Anna Smith" />
              </div>
              <div>
                <label className="mb-1.5 block text-[13px] font-semibold">Phone</label>
                <input className={fieldClass} defaultValue="+1 555 123 4567" />
              </div>
            </div>
            <div className="mb-5">
              <label className="mb-1.5 block text-[13px] font-semibold">Email</label>
              <input className={fieldClass} type="email" defaultValue="anna@example.com" />
            </div>
            <button type="submit" className="h-11 rounded-sm bg-brand px-6 text-[15px] font-semibold text-white hover:bg-brand-dark">
              Save changes
            </button>
          </form>
          <form className="rounded-lg border border-line bg-surface p-6" onSubmit={onSave}>
            <h2 className="text-[18px] font-semibold">Password</h2>
            <p className="mt-1 mb-5 text-[14px] text-muted">Use at least 8 characters.</p>
            <div className="mb-4">
              <label className="mb-1.5 block text-[13px] font-semibold">Current password</label>
              <input className={fieldClass} type="password" placeholder="••••••••" />
            </div>
            <div className="mb-5">
              <label className="mb-1.5 block text-[13px] font-semibold">New password</label>
              <input className={fieldClass} type="password" placeholder="••••••••" />
            </div>
            <button type="submit" className="h-11 rounded-sm border border-ink px-6 text-[15px] font-semibold hover:bg-ink hover:text-white">
              Change password
            </button>
          </form>
          <section className="rounded-lg border border-danger-soft bg-danger-soft/40 p-6">
            <h2 className="text-[18px] font-semibold">Delete account</h2>
            <p className="mt-1 mb-5 text-[14px] text-muted">Your profile will be deactivated. You will be signed out immediately.</p>
            <button type="button" className="inline-flex h-11 items-center gap-2 rounded-sm border border-danger-soft bg-danger-soft px-6 text-[15px] font-semibold text-danger hover:border-danger hover:bg-danger hover:text-white">
              <Trash2 size={18} /> Delete account
            </button>
          </section>
        </div>
      </div>
    </div>
  )
}
