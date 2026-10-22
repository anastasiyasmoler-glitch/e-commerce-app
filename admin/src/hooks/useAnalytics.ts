import { useQuery } from '@tanstack/react-query'
import { analyticsPeriodDays, fetchAnalyticsSummary } from '@/api/analyticsApi'

export function useAnalytics(periodLabel: string) {
  const days = analyticsPeriodDays[periodLabel] ?? 30

  return useQuery({
    queryKey: ['admin', 'analytics', days],
    queryFn: () => fetchAnalyticsSummary(days),
  })
}
