import { useQuery } from '@tanstack/react-query'
import { adminKeys, fetchAnalytics } from '@/api/adminApi'

export function useAnalytics() {
  return useQuery({
    queryKey: adminKeys.analytics,
    queryFn: fetchAnalytics,
  })
}
