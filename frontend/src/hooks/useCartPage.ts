import { useCart } from '@/lib/cart/CartContext'
import { formatUsd } from '@/lib/money/formatUsd'
import { paths } from '@/paths'

export function useCartPage() {
  const cart = useCart()

  return {
    lines: cart.lines,
    itemCount: cart.itemCount,
    subtotalCents: cart.subtotalCents,
    subtotalLabel: formatUsd(cart.subtotalCents),
    isEmpty: cart.lines.length === 0,
    setQuantity: cart.setQuantity,
    removeLine: cart.removeLine,
    checkoutPath: paths.checkout,
  }
}
