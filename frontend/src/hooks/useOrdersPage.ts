import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { listOrders, orderKeys } from '@/api/orderApi'
import { useSession } from '@/hooks/useSession'

export function useOrdersPage() {
  const [page, setPage] = useState(1)
  const session = useSession()

  const query = useQuery({
    queryKey: orderKeys.list(page),
    queryFn: () => listOrders(page),
    enabled: session.data === true,
  })

  return {
    page,
    setPage,
    query,
    orders: query.data?.data ?? [],
    meta: query.data?.meta,
    needsAuth: session.isFetched && session.data === false,
  }
}
