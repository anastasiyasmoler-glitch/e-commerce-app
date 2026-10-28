import { useMutation, useQueryClient } from '@tanstack/react-query'
import toast from 'react-hot-toast'
import { useNavigate } from 'react-router-dom'
import { createOrder, orderKeys } from '@/api/orderApi'
import { apiErrorMessage } from '@/api/http'
import { useCart } from '@/lib/cart/CartContext'
import { orderPath } from '@/paths'

export function useCheckoutPage() {
  const cart = useCart()
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  const placeOrder = useMutation({
    mutationFn: async (shipping: { line1: string; city: string; postal_code: string; country: string }) => {
      const idempotencyKey = crypto.randomUUID()
      return createOrder(
        {
          items: cart.lines.map((line) => ({
            product_id: line.productId,
            quantity: line.quantity,
          })),
          shipping_address: shipping,
          currency: 'USD',
        },
        idempotencyKey,
      )
    },
    onSuccess: (order) => {
      cart.clear()
      void queryClient.invalidateQueries({ queryKey: orderKeys.all })
      toast.success('Order placed')
      navigate(orderPath(order.id))
    },
    onError: (error) => {
      toast.error(apiErrorMessage(error))
    },
  })

  return {
    lines: cart.lines,
    subtotalCents: cart.subtotalCents,
    isEmpty: cart.lines.length === 0,
    placeOrder,
  }
}
