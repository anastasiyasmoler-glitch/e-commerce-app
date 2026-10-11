import { Search, ShoppingCart, Sparkles, User } from 'lucide-react'
import { type FormEvent } from 'react'
import { Link } from 'react-router-dom'
import { getDisplayName } from '../../auth/session'

const categories = ['Sofas', 'Beds', 'Tables', 'Chairs', 'Storage', 'Lighting', 'Textiles', 'Decor']

type StoreHeaderProps = {
  signedInName: string | null
  onSignIn: () => void
  onRegister: () => void
}

export function StoreHeader({ signedInName, onSignIn, onRegister }: StoreHeaderProps) {
  const name = signedInName ?? getDisplayName()

  function onSearch(event: FormEvent) {
    event.preventDefault()
  }

  return (
    <>
      <header className="sticky top-0 z-20 border-b border-line bg-surface">
        <div className="mx-auto flex h-[68px] max-w-[1240px] items-center gap-6 px-6">
          <Link to="/" className="font-display text-[22px] font-medium tracking-tight text-ink">
            Domo<span className="text-brand">.</span>
          </Link>
          <form className="relative min-w-0 flex-1" role="search" onSubmit={onSearch}>
            <Search className="pointer-events-none absolute top-1/2 left-3.5 size-[18px] -translate-y-1/2 text-muted" aria-hidden />
            <input
              className="h-11 w-full rounded-sm border border-line-strong bg-surface pr-4 pl-10 text-[15px] outline-none placeholder:text-muted/70 focus:border-brand focus:shadow-[0_0_0_3px_var(--brand-glow)]"
              type="search"
              placeholder="Search furniture and home goods"
              aria-label="Search products"
            />
          </form>
          <nav className="flex shrink-0 items-center gap-4 text-[13px] font-medium" aria-label="Account">
            <span className="inline-flex items-center gap-1.5 text-ink">
              <Sparkles size={18} aria-hidden />
              <span className="hidden lg:inline">AI Assistant</span>
            </span>
            {name ? (
              <Link to="/profile" className="inline-flex items-center gap-1.5">
                <User size={18} aria-hidden />
                <span className="hidden lg:inline">{name}</span>
              </Link>
            ) : (
              <>
                <button type="button" className="inline-flex items-center gap-1.5" onClick={onSignIn}>
                  <User size={18} aria-hidden />
                  <span className="hidden lg:inline">Sign in</span>
                </button>
                <button
                  type="button"
                  className="hidden h-9 rounded-sm bg-brand px-4 text-[13px] font-semibold text-white hover:bg-brand-dark sm:inline-flex sm:items-center"
                  onClick={onRegister}
                >
                  Create account
                </button>
              </>
            )}
            <span className="inline-flex items-center gap-1.5">
              <ShoppingCart size={18} aria-hidden />
              <span className="hidden lg:inline">Cart</span>
              <span className="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-brand px-1 text-[11px] font-semibold text-white">
                3
              </span>
            </span>
          </nav>
        </div>
      </header>
      <nav className="border-b border-line bg-surface" aria-label="Categories">
        <div className="mx-auto flex max-w-[1240px] gap-6 overflow-x-auto px-6 py-3 text-[14px]">
          {categories.map((label) => (
            <span key={label} className="shrink-0 text-ink/80">
              {label}
            </span>
          ))}
        </div>
      </nav>
    </>
  )
}
