import { Link } from 'react-router-dom'
import { Minus, Plus, Trash2 } from 'lucide-react'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { useCartPage } from '@/hooks/useCartPage'
import { paths, productPath } from '@/paths'

export function CartPage() {
  const cart = useCartPage()

  return (
    <Container className="py-8">
      <Breadcrumb className="mb-4">
        <BreadcrumbList>
          <BreadcrumbItem>
            <BreadcrumbLink asChild>
              <Link to={paths.home}>Home</Link>
            </BreadcrumbLink>
          </BreadcrumbItem>
          <BreadcrumbSeparator />
          <BreadcrumbItem>
            <BreadcrumbPage>Cart</BreadcrumbPage>
          </BreadcrumbItem>
        </BreadcrumbList>
      </Breadcrumb>

      <h1 className="mb-8 font-display text-[36px] font-medium">Cart</h1>

      {cart.isEmpty ? (
        <div className="rounded-[12px] border border-line bg-subtle px-6 py-16 text-center">
          <p className="text-muted">Your cart is empty.</p>
          <Button asChild className="mt-6 rounded-[6px] bg-brand text-white hover:bg-brand-dark">
            <Link to={paths.catalog}>Browse catalog</Link>
          </Button>
        </div>
      ) : (
        <div className="grid gap-10 lg:grid-cols-[1fr_320px]">
          <ul className="divide-y divide-line rounded-[12px] border border-line">
            {cart.lines.map((line) => (
              <li key={line.productId} className="flex flex-wrap items-center gap-4 p-4 sm:flex-nowrap">
                <div className="min-w-0 flex-1">
                  <Link
                    to={productPath(line.productId)}
                    className="font-medium text-ink hover:text-brand"
                  >
                    {line.name}
                  </Link>
                  <p className="text-[13px] text-muted">
                    ${(line.priceCents / 100).toFixed(2)} each
                  </p>
                </div>
                <div className="flex items-center gap-2">
                  <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    className="size-9 rounded-[6px]"
                    aria-label="Decrease quantity"
                    onClick={() => cart.setQuantity(line.productId, line.quantity - 1)}
                  >
                    <Minus className="size-4" />
                  </Button>
                  <span className="w-8 text-center text-[15px] font-medium">{line.quantity}</span>
                  <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    className="size-9 rounded-[6px]"
                    aria-label="Increase quantity"
                    onClick={() => cart.setQuantity(line.productId, line.quantity + 1)}
                  >
                    <Plus className="size-4" />
                  </Button>
                </div>
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  className="size-9 text-muted hover:text-danger"
                  aria-label="Remove item"
                  onClick={() => cart.removeLine(line.productId)}
                >
                  <Trash2 className="size-4" />
                </Button>
              </li>
            ))}
          </ul>
          <aside className="h-fit rounded-[12px] border border-line p-6">
            <div className="flex items-baseline justify-between text-[15px]">
              <span className="text-muted">Subtotal</span>
              <span className="text-[18px] font-semibold">{cart.subtotalLabel}</span>
            </div>
            <p className="mt-2 text-[13px] text-muted">Shipping and tax calculated at checkout.</p>
            <Button asChild className="mt-6 h-12 w-full rounded-[6px] bg-brand text-[16px] font-semibold text-white hover:bg-brand-dark">
              <Link to={paths.checkout}>Proceed to checkout</Link>
            </Button>
          </aside>
        </div>
      )}
    </Container>
  )
}
