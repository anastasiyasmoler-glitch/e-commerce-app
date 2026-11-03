import { useMutation } from '@tanstack/react-query'
import { useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import toast from 'react-hot-toast'
import { createOrder } from '@/api/orderApi'
import { apiErrorMessage } from '@/api/http'
import { useCart } from '@/lib/cart/CartContext'
import { waitForOrderStatus } from '@/lib/order/waitForOrderStatus'
import { useProfile } from '@/hooks/useProfile'
import { useSession } from '@/hooks/useSession'

export function useCheckoutPage() {
  const cart = useCart()
  const session = useSession()
  const profile = useProfile()
  const [searchParams, setSearchParams] = useSearchParams()
  const [payOrderId, setPayOrderId] = useState<number | null>(null)

  const placeOrder = useMutation({
    mutationFn: async () => {
      const idempotencyKey = crypto.randomUUID()
      const order = await createOrder(
        cart.lines.map((line) => ({
          product_id: line.productId,
          quantity: line.quantity,
        })),
        idempotencyKey,
      )

      const waited = await waitForOrderStatus(order.id, ['payment_pending'])

      if (waited.status !== 'payment_pending') {
        if (waited.status === 'payment_failed') {
          throw new Error(
            'Payment could not be set up. Check STRIPE_SECRET in order/.env and restart the order stack.',
          )
        }
        throw new Error(`Order cannot be paid (status: ${waited.status}).`)
      }

      return order.id
    },
    onSuccess: (orderId) => {
      setPayOrderId(orderId)
    },
    onError: (error) => {
      toast.error(apiErrorMessage(error))
    },
  })

  const needsAuth = session.isFetched && session.data === false
  const canCheckout = session.data === true && cart.lines.length > 0
  const isBusy = placeOrder.isPending
  const checkoutError = placeOrder.isError ? apiErrorMessage(placeOrder.error) : null

  const dismissPaymentNotice = () => {
    const next = new URLSearchParams(searchParams)
    next.delete('payment_canceled')
    setSearchParams(next, { replace: true })
  }

  const startCheckout = () => {
    placeOrder.reset()
    setPayOrderId(null)
    placeOrder.mutate()
  }

  const paymentCanceled = searchParams.get('payment_canceled') === '1'

  return {
    lines: cart.lines,
    itemCount: cart.itemCount,
    needsAuth,
    canCheckout,
    profile: profile.data,
    profilePending: profile.isPending,
    isBusy,
    checkoutError,
    payOrderId,
    paymentCanceled,
    dismissPaymentNotice,
    startCheckout,
  }
}
