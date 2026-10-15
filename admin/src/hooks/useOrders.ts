import { useQuery } from '@tanstack/react-query'
import { adminKeys, fetchOrders } from '@/api/adminApi'

export function useOrders() {
  return useQuery({
    queryKey: adminKeys.orders,
    queryFn: fetchOrders,
  })
}
