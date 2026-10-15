import { Search, ShoppingCart, Sparkles, User } from 'lucide-react'
import { type FormEvent } from 'react'
import { Link, NavLink } from 'react-router-dom'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { readDisplayName } from '@/auth/displayName'
import { useSelectedStore } from '@/catalog/SelectedStoreContext'
import { useCategories } from '@/hooks/useCategories'
import { useMe } from '@/hooks/useMe'
import { useSession } from '@/hooks/useSession'
import { categoryPath, paths } from '@/paths'

type StoreHeaderProps = {
  onSignIn: () => void
  onRegister: () => void
}

export function StoreHeader({ onSignIn, onRegister }: StoreHeaderProps) {
  const session = useSession()
  const me = useMe()
  const { selectedStore, isPending: storesPending } = useSelectedStore()
  const categoriesQuery = useCategories(selectedStore?.id)
  const categories = (categoriesQuery.data ?? []).filter((category) => category.parent_id === null)
  const categoriesLoading = storesPending || categoriesQuery.isPending || !categoriesQuery.isSuccess
  const signedOut = session.isFetched && session.data === false
  const name = me.data?.name ?? (signedOut ? null : readDisplayName())

  function onSearch(event: FormEvent) {
    event.preventDefault()
  }

  return (
    <>
      <header className="sticky top-0 z-20 border-b border-line bg-surface/96 backdrop-blur-md">
        <Container className="flex h-[68px] items-center gap-6">
          <Link
            to={paths.home}
            className="font-display text-[28px] leading-none font-semibold tracking-[-0.03em] text-brand"
          >
            Domo<i className="not-italic text-accent">.</i>
          </Link>
          <form
            className="relative ml-auto flex-initial basis-[280px]"
            role="search"
            onSubmit={onSearch}
          >
            <Search
              className="pointer-events-none absolute top-1/2 left-[11px] size-4 -translate-y-1/2 text-muted"
              aria-hidden
            />
            <input
              className="h-9 w-full rounded-[6px] border border-line bg-transparent pr-3 pl-9 text-[13px] outline-none transition-all placeholder:text-muted/70 focus:border-brand focus:bg-surface focus:ring-3 focus:ring-brand/14"
              type="search"
              placeholder="Search furniture and home goods"
              aria-label="Search products"
            />
          </form>
          <nav className="flex shrink-0 items-center gap-6 text-[13px] font-medium" aria-label="Account">
            <button
              type="button"
              className="inline-flex items-center gap-2 text-ink transition-colors hover:text-brand"
            >
              <Sparkles className="size-5" />
              <span className="hidden lg:inline">AI Assistant</span>
            </button>
            {signedOut ? (
              <>
                <button
                  type="button"
                  onClick={onSignIn}
                  className="inline-flex items-center gap-2 text-ink transition-colors hover:text-brand"
                >
                  <User className="size-5" />
                  <span className="hidden lg:inline">Sign in</span>
                </button>
                <Button
                  type="button"
                  onClick={onRegister}
                  className="ml-2 hidden h-9 rounded-[6px] bg-brand px-4 text-[13px] font-semibold text-white hover:bg-brand-dark lg:inline-flex"
                >
                  Create account
                </Button>
              </>
            ) : (
              <Link
                to={paths.profile}
                className="inline-flex items-center gap-2 text-ink transition-colors hover:text-brand"
              >
                <User className="size-5" />
                <span className="hidden lg:inline">{name}</span>
              </Link>
            )}
            <button
              type="button"
              className="relative inline-flex items-center gap-2 text-ink transition-colors hover:text-brand"
            >
              <ShoppingCart className="size-5" />
              <span className="hidden lg:inline">Cart</span>
              <span className="absolute -top-2 left-3 grid h-[18px] min-w-[18px] place-items-center rounded-[9px] bg-brand px-[5px] text-[11px] font-bold text-white">
                3
              </span>
            </button>
          </nav>
        </Container>
      </header>
      {categoriesLoading ? null : (
        <nav className="border-b border-line bg-surface" aria-label="Categories">
          <Container className="flex gap-8 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            {categories.map((category) => (
              <NavLink
                key={category.id}
                to={categoryPath(category.slug)}
                className={({ isActive }) =>
                  `shrink-0 py-[14px] text-[13px] whitespace-nowrap transition-colors ${
                    isActive ? 'font-semibold text-ink' : 'font-medium text-muted hover:text-ink'
                  }`
                }
              >
                {category.name}
              </NavLink>
            ))}
          </Container>
        </nav>
      )}
    </>
  )
}
