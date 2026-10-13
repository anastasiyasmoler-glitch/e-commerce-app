import { useQuery } from '@tanstack/react-query'
import { TrendingDown, TrendingUp } from 'lucide-react'
import { useState } from 'react'
import { adminKeys, fetchAnalytics } from '@/api/adminApi'
import { PageHeader } from '@/components/ui/page-header'

const periods = ['Last 7 days', 'Last 30 days', 'Last 90 days']

export function AnalyticsPage() {
  const [period, setPeriod] = useState(periods[0])
  const analytics = useQuery({ queryKey: adminKeys.analytics, queryFn: fetchAnalytics })

  const data = analytics.data

  return (
    <>
      <PageHeader
        title="Analytics"
        actions={
          <select
            className="select"
            aria-label="Period"
            value={period}
            onChange={(event) => setPeriod(event.target.value)}
          >
            {periods.map((item) => (
              <option key={item}>{item}</option>
            ))}
          </select>
        }
      />
      <div className="kpi-grid">
        {(data?.kpis ?? []).map((kpi) => (
          <div className="kpi-card" key={kpi.label}>
            <div className="kpi-label">{kpi.label}</div>
            <div className="kpi-value">{kpi.value}</div>
            <div className={`kpi-delta ${kpi.up ? 'up' : 'down'}`}>
              {kpi.up ? <TrendingUp size={14} aria-hidden /> : <TrendingDown size={14} aria-hidden />}
              {kpi.delta}
            </div>
          </div>
        ))}
      </div>
      <div className="chart-grid">
        <div className="chart-card">
          <div className="chart-header">
            <div>
              <div className="chart-title">User growth</div>
              <div className="chart-sub">Registrations per week</div>
            </div>
          </div>
          <svg
            className="line-chart"
            viewBox="0 0 560 210"
            preserveAspectRatio="none"
            role="img"
            aria-label="Registrations over time"
          >
            <polygon points={data?.userGrowth.area} fill="var(--brand-soft)" />
            <polyline
              points={data?.userGrowth.points}
              fill="none"
              stroke="var(--brand)"
              strokeWidth="2.5"
              strokeLinejoin="round"
              strokeLinecap="round"
            />
          </svg>
        </div>
        <div className="chart-card">
          <div className="chart-header">
            <div>
              <div className="chart-title">Order funnel</div>
              <div className="chart-sub">created → paid → completed / cancelled</div>
            </div>
          </div>
          {(data?.funnel ?? []).map((step) => (
            <div className="funnel-step" key={step.label}>
              <div className="funnel-top">
                <span>{step.label}</span>
                <b>{step.value}</b>
              </div>
              <div className={`funnel-track${step.bad ? ' bad' : ''}`}>
                <i style={{ width: `${step.width}%` }}></i>
              </div>
            </div>
          ))}
        </div>
      </div>
      <div className="chart-grid even">
        <div className="chart-card">
          <div className="chart-header">
            <div>
              <div className="chart-title">Revenue</div>
              <div className="chart-sub">By day</div>
            </div>
          </div>
          <div className="bar-chart">
            {(data?.revenue ?? []).map((bar) => (
              <div className="bar-chart-item" key={bar.day}>
                <span className="bar-chart-value">{bar.value}</span>
                <div className="bar-chart-bar" style={{ height: `${bar.height}%` }}></div>
                <span>{bar.day}</span>
              </div>
            ))}
          </div>
        </div>
        <div className="chart-card">
          <div className="chart-title">Popular searches</div>
          <div className="ranklist" style={{ margin: 'var(--space-3) 0 var(--space-6)' }}>
            {(data?.popularSearches ?? []).map((item) => (
              <div key={item.term}>
                <span>{item.term}</span>
                <b>{item.count}</b>
              </div>
            ))}
          </div>
          <div className="chart-title">Searches with no results</div>
          <div className="ranklist" style={{ marginTop: 'var(--space-3)' }}>
            {(data?.emptySearches ?? []).map((item) => (
              <div key={item.term}>
                <span>{item.term}</span>
                <small>{item.count}</small>
              </div>
            ))}
          </div>
        </div>
      </div>
    </>
  )
}
