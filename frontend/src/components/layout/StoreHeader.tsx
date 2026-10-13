import { Search, ShoppingCart, Sparkles, User } from 'lucide-react'
import { type FormEvent } from 'react'
import { Link } from 'react-router-dom'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { Skeleton } from '@/components/ui/skeleton'
import { Input } from '@/components/ui/input'
import { readDisplayName } from '@/auth/displayName'
import { catalogKeys, fetchCategories } from '@/api/catalogApi'
import { useSelectedStore } from '@/catalog/SelectedStoreContext'
import { useQuery } from '@tanstack/react-query'
import { useMe } from '@/hooks/useMe'
import { useSession } from '@/hooks/useSession'
import { paths } from '@/paths'

type StoreHeaderProps = {
  onSignIn: () => void
  onRegister: () => void
}

export function StoreHeader({ onSignIn, onRegister }: StoreHeaderProps) {
  const session = useSession()
  const me = useMe()
  const { selectedStore } = useSelectedStore()
  const categoriesQuery = useQuery({
    queryKey: catalogKeys.categories(selectedStore?.id),
    queryFn: () => fetchCategories(selectedStore!.id, null),
    enabled: selectedStore !== null,
  })
  const categories = (categoriesQuery.data ?? []).filter((category) => category.parent_id === null)
  const signedOut = session.isFetched && session.data === false
  const name = me.data?.name ?? (signedOut ? null : readDisplayName())

  function onSearch(event: FormEvent) {
    event.preventDefault()
  }

  return (
    <>
      <header className="sticky top-0 z-20 border-b border-line bg-surface">
        <Container className="flex h-[68px] items-center gap-6">
          <Link to={paths.home} className="font-display text-[22px] font-medium tracking-tight text-ink">
            Domo<span className="text-brand">.</span>
          </Link>
          <form className="relative min-w-0 flex-1" role="search" onSubmit={onSearch}>
            <Search className="pointer-events-none absolute top-1/2 left-3.5 size-[18px] -translate-y-1/2 text-muted" aria-hidden />
            <Input
              className="pl-10"
              type="search"
              placeholder="Search furniture and home goods"
              aria-label="Search products"
            />
          </form>
          <nav className="flex shrink-0 items-center gap-2 text-[13px] font-medium" aria-label="Account">
            <Button type="button" variant="ghost" className="hidden sm:inline-flex">
              <Sparkles />
              <span className="hidden lg:inline">AI Assistant</span>
            </Button>
            {signedOut ? (
              <>
                <Button type="button" variant="ghost" onClick={onSignIn}>
                  <User />
                  <span className="hidden lg:inline">Sign in</span>
                </Button>
                <Button type="button" className="hidden sm:inline-flex" onClick={onRegister}>
                  Create account
                </Button>
              </>
            ) : (
              <Button variant="ghost" asChild>
                <Link to={paths.profile}>
                  <User />
                  <span className="hidden lg:inline">{name}</span>
                </Link>
              </Button>
            )}
            <Button type="button" variant="ghost">
              <ShoppingCart />
              <span className="hidden lg:inline">Cart</span>
              <Badge>3</Badge>
            </Button>
          </nav>
        </Container>
      </header>
      <nav className="border-b border-line bg-surface" aria-label="Categories">
        <Container className="flex gap-6 overflow-x-auto py-3 text-[14px]">
          {categoriesQuery.isLoading
            ? Array.from({ length: 6 }, (_, index) => (
                <Skeleton key={index} className="h-[20px] w-20" />
              ))
            : categories.map((category) => (
                <span key={category.id} className="shrink-0 text-ink/80">
                  {category.name}
                </span>
              ))}
        </Container>
      </nav>
    </>
  )
}
