import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useParams } from 'react-router-dom'
import toast from 'react-hot-toast'
import { cancelOrder, fetchOrder, orderKeys } from '@/api/orderApi'
import { apiErrorMessage } from '@/api/http'
import { canCancelOrder } from '@/lib/order/orderStatus'

export function useOrderDetailPage() {
  const { id } = useParams()
  const orderId = Number(id)
  const validId = Number.isFinite(orderId) && orderId > 0 ? orderId : undefined
  const queryClient = useQueryClient()

  const orderQuery = useQuery({
    queryKey: orderKeys.detail(validId ?? 0),
    queryFn: () => fetchOrder(validId!),
    enabled: validId !== undefined,
  })

  const cancel = useMutation({
    mutationFn: () => cancelOrder(validId!),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: orderKeys.detail(validId!) })
      void queryClient.invalidateQueries({ queryKey: orderKeys.list() })
      toast.success('Order cancelled')
    },
    onError: (error) => {
      toast.error(apiErrorMessage(error))
    },
  })

  const order = orderQuery.data
  const canCancel = order ? canCancelOrder(order.status) : false

  return {
    orderId: validId,
    order,
    isPending: orderQuery.isPending,
    isNotFound: orderQuery.isError || (orderQuery.isSuccess && !order),
    canCancel,
    cancel,
  }
}
