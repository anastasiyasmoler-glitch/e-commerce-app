import { useMutation } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import toast from 'react-hot-toast'
import { apiErrorMessage } from '@/api/http'
import { createOrder } from '@/api/orderApi'
import { useCart } from '@/lib/cart/CartContext'
import { useSession } from '@/hooks/useSession'
import { orderPath } from '@/paths'

export function useCheckoutPage() {
  const cart = useCart()
  const session = useSession()
  const navigate = useNavigate()

  const placeOrder = useMutation({
    mutationFn: () => {
      const idempotencyKey = crypto.randomUUID()
      return createOrder(
        cart.lines.map((line) => ({
          product_id: line.productId,
          quantity: line.quantity,
        })),
        idempotencyKey,
      )
    },
    onSuccess: (order) => {
      cart.clear()
      toast.success('Order placed')
      void navigate(orderPath(order.id))
    },
    onError: (error) => {
      toast.error(apiErrorMessage(error))
    },
  })

  const needsAuth = session.isFetched && session.data === false
  const canCheckout = session.data === true && cart.lines.length > 0

  return {
    lines: cart.lines,
    itemCount: cart.itemCount,
    needsAuth,
    canCheckout,
    placeOrder,
  }
}
