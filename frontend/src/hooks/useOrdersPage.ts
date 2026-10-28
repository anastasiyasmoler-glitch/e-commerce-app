import { useQuery } from '@tanstack/react-query'
import { fetchOrders, orderKeys } from '@/api/orderApi'

export function useOrdersPage() {
  const ordersQuery = useQuery({
    queryKey: orderKeys.list(),
    queryFn: () => fetchOrders(1),
  })

  return {
    orders: ordersQuery.data?.data ?? [],
    isPending: ordersQuery.isPending,
    isSuccess: ordersQuery.isSuccess,
    isError: ordersQuery.isError,
  }
}
