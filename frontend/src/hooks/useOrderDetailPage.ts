import { useQuery } from '@tanstack/react-query'
import { useParams } from 'react-router-dom'
import { getOrder, orderKeys } from '@/api/orderApi'
import { useSession } from '@/hooks/useSession'

export function useOrderDetailPage() {
  const { id } = useParams()
  const orderId = Number(id)
  const validId = Number.isFinite(orderId) && orderId > 0
  const session = useSession()

  const query = useQuery({
    queryKey: orderKeys.detail(validId ? orderId : 0),
    queryFn: () => getOrder(orderId),
    enabled: session.data === true && validId,
  })

  return {
    orderId: validId ? orderId : null,
    query,
    order: query.data,
    needsAuth: session.isFetched && session.data === false,
    invalidId: !validId,
  }
}
