import { useCart } from '@/lib/cart/CartContext'
import { formatOrderMoney } from '@/lib/order/orderStatus'

export function useCartPage() {
  const cart = useCart()

  return {
    lines: cart.lines,
    itemCount: cart.itemCount,
    subtotalLabel: formatOrderMoney(cart.subtotalCents, 'USD'),
    setQuantity: cart.setQuantity,
    removeLine: cart.removeLine,
    isEmpty: cart.lines.length === 0,
  }
}
