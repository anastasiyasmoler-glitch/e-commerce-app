import { http } from '@/api/http'

export type AnalyticsSummary = {
  kpis: { label: string; value: string; delta: string; up: boolean }[]
  userGrowth: { points: string; area: string }
  funnel: { label: string; value: string; width: number; bad?: boolean }[]
  revenue: { day: string; value: string; height: number }[]
  popularSearches: { term: string; count: string }[]
  emptySearches: { term: string; count: string }[]
}

type PeriodMeta = { from: string; to: string }

type UserMetricsResponse = {
  data: { total: number; by_day: { date: string; count: number }[] }
  meta: PeriodMeta
}

type OrderMetricsResponse = {
  data: {
    created: number
    paid: number
    cancelled: number
    conversion_rate: number | null
    revenue_cents: number
    average_order_value_cents: number | null
    currency: string | null
    revenue_by_day: { date: string; revenue_cents: number }[]
  }
  meta: PeriodMeta
}

type SearchMetricsResponse = {
  data: {
    total: number
    top_queries: { query: string; count: number }[]
    zero_result_total: number
    zero_result_queries: { query: string; count: number }[]
  }
  meta: PeriodMeta
}

const numberFmt = new Intl.NumberFormat('en-US')

function periodRange(days: number): { from: string; to: string } {
  const to = new Date()
  const from = new Date()
  from.setUTCDate(from.getUTCDate() - (days - 1))

  return {
    from: from.toISOString().slice(0, 10),
    to: to.toISOString().slice(0, 10),
  }
}

function formatMoney(cents: number, currency: string | null): string {
  const amount = cents / 100
  try {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency ?? 'USD',
      maximumFractionDigits: 0,
    }).format(amount)
  } catch {
    return `$${amount.toFixed(0)}`
  }
}

function buildLineChart(byDay: { date: string; count: number }[]): { points: string; area: string } {
  const width = 560
  const height = 200
  const padding = 10

  if (byDay.length === 0) {
    const baseline = `${padding},${height} ${width - padding},${height}`
    return { points: baseline, area: `${baseline} ${width - padding},${height + padding} ${padding},${height + padding}` }
  }

  const max = Math.max(...byDay.map((row) => row.count), 1)
  const step = byDay.length > 1 ? (width - padding * 2) / (byDay.length - 1) : 0

  const coords = byDay.map((row, index) => {
    const x = padding + step * index
    const y = height - padding - (row.count / max) * (height - padding * 2)
    return { x, y }
  })

  const points = coords.map(({ x, y }) => `${x.toFixed(1)},${y.toFixed(1)}`).join(' ')
  const area = `${points} ${coords[coords.length - 1].x.toFixed(1)},${height} ${coords[0].x.toFixed(1)},${height}`

  return { points, area }
}

function buildRevenueBars(rows: { date: string; revenue_cents: number }[]): AnalyticsSummary['revenue'] {
  if (rows.length === 0) {
    return []
  }

  const max = Math.max(...rows.map((row) => row.revenue_cents), 1)

  return rows.map((row) => {
    const day = new Date(`${row.date}T00:00:00Z`).toLocaleDateString('en-US', { weekday: 'short' })
    return {
      day,
      value: formatMoney(row.revenue_cents, 'USD'),
      height: Math.max(8, Math.round((row.revenue_cents / max) * 100)),
    }
  })
}

function mapToSummary(
  users: UserMetricsResponse,
  orders: OrderMetricsResponse,
  search: SearchMetricsResponse,
): AnalyticsSummary {
  const { data: u, meta } = users
  const { data: o } = orders
  const { data: s } = search

  const paidPct = o.created > 0 ? Math.round((o.paid / o.created) * 100) : 0
  const cancelledPct = o.created > 0 ? Math.round((o.cancelled / o.created) * 100) : 0

  return {
    kpis: [
      {
        label: 'Revenue',
        value: formatMoney(o.revenue_cents, o.currency),
        delta: `${meta.from} – ${meta.to}`,
        up: true,
      },
      {
        label: 'Orders (paid)',
        value: numberFmt.format(o.paid),
        delta: `${numberFmt.format(o.created)} created`,
        up: o.paid >= o.cancelled,
      },
      {
        label: 'New users',
        value: numberFmt.format(u.total),
        delta: `${meta.from} – ${meta.to}`,
        up: true,
      },
      {
        label: 'Average order value',
        value: o.average_order_value_cents != null
          ? formatMoney(o.average_order_value_cents, o.currency)
          : '—',
        delta: o.conversion_rate != null ? `${(o.conversion_rate * 100).toFixed(1)}% paid / created` : '—',
        up: (o.conversion_rate ?? 0) >= 0.5,
      },
    ],
    userGrowth: buildLineChart(u.by_day),
    funnel: [
      { label: 'Created', value: `${numberFmt.format(o.created)} · 100%`, width: 100 },
      { label: 'Paid', value: `${numberFmt.format(o.paid)} · ${paidPct}%`, width: Math.max(paidPct, 4) },
      {
        label: 'Cancelled',
        value: `${numberFmt.format(o.cancelled)} · ${cancelledPct}%`,
        width: Math.max(cancelledPct, 4),
        bad: true,
      },
    ],
    revenue: buildRevenueBars(o.revenue_by_day),
    popularSearches: s.top_queries.map((row) => ({
      term: row.query,
      count: numberFmt.format(row.count),
    })),
    emptySearches: s.zero_result_queries.map((row) => ({
      term: row.query,
      count: `${numberFmt.format(row.count)} searches`,
    })),
  }
}

export async function fetchAnalyticsSummary(periodDays: number): Promise<AnalyticsSummary> {
  const { from, to } = periodRange(periodDays)
  const params = { from, to }

  const [usersRes, ordersRes, searchRes] = await Promise.all([
    http.get<UserMetricsResponse>('/api/analytics/users', { params }),
    http.get<OrderMetricsResponse>('/api/analytics/orders', { params }),
    http.get<SearchMetricsResponse>('/api/analytics/search', { params }),
  ])

  return mapToSummary(usersRes.data, ordersRes.data, searchRes.data)
}

export const analyticsPeriodDays: Record<string, number> = {
  'Last 7 days': 7,
  'Last 30 days': 30,
  'Last 90 days': 90,
}
