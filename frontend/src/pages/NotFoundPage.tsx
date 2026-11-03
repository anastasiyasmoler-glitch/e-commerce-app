import { Link } from 'react-router-dom'
import { SearchX } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { paths } from '@/paths'

export function NotFoundPage() {
  return (
    <Container className="py-24 text-center">
      <SearchX aria-hidden className="mx-auto size-12 text-line-strong" strokeWidth={1.5} />
      <h1 className="mt-6 font-display text-[36px] font-medium tracking-[-0.02em]">
        Page not found
      </h1>
      <p className="mx-auto mt-3 max-w-md text-[15px] text-muted">
        This URL does not match anything on the storefront. Check the address or return home.
      </p>
      <Button asChild className="mt-8 rounded-[6px] bg-brand text-white hover:bg-brand-dark">
        <Link to={paths.home}>Back to home</Link>
      </Button>
    </Container>
  )
}
