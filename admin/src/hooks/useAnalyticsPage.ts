import { useState } from 'react'
import { useAnalytics } from '@/hooks/useAnalytics'

export const ANALYTICS_PERIOD_OPTIONS = ['Last 7 days', 'Last 30 days', 'Last 90 days'] as const

export function useAnalyticsPage() {
  const [period, setPeriod] = useState<string>(ANALYTICS_PERIOD_OPTIONS[0])
  const analytics = useAnalytics(period)

  return {
    period,
    setPeriod,
    analytics,
    data: analytics.data,
    periodOptions: ANALYTICS_PERIOD_OPTIONS,
  }
}
