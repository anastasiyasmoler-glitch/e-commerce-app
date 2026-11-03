import { useCart } from '@/lib/cart/CartContext'
import { formatOrderMoney } from '@/lib/order/orderStatus'

export function useCartPage() {
  const cart = useCart()

  return {
    lines: cart.lines,
    itemCount: cart.itemCount,
    itemCountLabel:
      cart.itemCount === 1 ? '1 item in your cart' : `${cart.itemCount} items in your cart`,
    subtotalLabel: formatOrderMoney(cart.subtotalCents, 'USD'),
    setQuantity: cart.setQuantity,
    removeLine: cart.removeLine,
    clearCart: cart.clear,
    isEmpty: cart.lines.length === 0,
  }
}
