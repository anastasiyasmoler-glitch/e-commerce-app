import { useEffect } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useParams, useSearchParams } from 'react-router-dom'
import toast from 'react-hot-toast'
import { cancelOrder, getOrder, orderKeys } from '@/api/orderApi'
import { apiErrorMessage } from '@/api/http'
import {
  canCancelOrder,
  orderConfirmationBanner,
  orderTimelineProgress,
} from '@/lib/order/orderTimeline'
import { useCart } from '@/lib/cart/CartContext'
import { useProfile } from '@/hooks/useProfile'
import { useSession } from '@/hooks/useSession'

const POLL_STATUSES = new Set([
  'created',
  'stock_reserved',
  'payment_pending',
  'paid',
])

export function useOrderConfirmationPage() {
  const { id } = useParams()
  const [searchParams] = useSearchParams()
  const returnedFromStripe = searchParams.get('from_stripe') === '1'
  const orderId = Number(id)
  const validId = Number.isFinite(orderId) && orderId > 0
  const session = useSession()
  const profile = useProfile()
  const cart = useCart()
  const queryClient = useQueryClient()

  useEffect(() => {
    if (returnedFromStripe) {
      cart.clear()
    }
  }, [returnedFromStripe, cart.clear])

  const query = useQuery({
    queryKey: orderKeys.detail(validId ? orderId : 0),
    queryFn: () => getOrder(orderId),
    enabled: session.data === true && validId,
    refetchInterval: (q) => {
      const status = q.state.data?.status
      if (!status || !POLL_STATUSES.has(status)) {
        return false
      }
      return 2000
    },
  })

  const cancel = useMutation({
    mutationFn: () => cancelOrder(orderId),
    onSuccess: (order) => {
      queryClient.setQueryData(orderKeys.detail(orderId), order)
      toast.success('Order cancelled.')
    },
    onError: (error) => {
      toast.error(apiErrorMessage(error))
    },
  })

  const order = query.data
  const banner = order
    ? orderConfirmationBanner(order.status, { returnedFromStripe })
    : null
  const timeline = order ? orderTimelineProgress(order.status) : null

  return {
    orderId: validId ? orderId : null,
    query,
    order,
    profileEmail: profile.data?.email,
    needsAuth: session.isFetched && session.data === false,
    invalidId: !validId,
    banner,
    timeline,
    canCancel: order ? canCancelOrder(order.status) : false,
    cancel,
  }
}
