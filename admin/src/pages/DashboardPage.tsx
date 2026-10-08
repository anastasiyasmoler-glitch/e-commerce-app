import { Download, DollarSign, Package, Plus, ShoppingCart, TrendingDown, TrendingUp, Users } from 'lucide-react'

const kpis = [
  { label: 'Revenue', value: '$48.5k', delta: '+12.5% vs last week', up: true, icon: DollarSign },
  { label: 'Orders', value: '1,248', delta: '+8.2% vs last week', up: true, icon: ShoppingCart },
  { label: 'New Customers', value: '384', delta: '+23.1% vs last week', up: true, icon: Users },
  { label: 'Avg. Order Value', value: '$38.90', delta: '-2.4% vs last week', up: false, icon: Package },
]

const recent = [
  { id: '#1042', customer: 'Ada Lovelace', date: '7 Oct 2026', total: '$129', status: 'Delivered' },
  { id: '#1041', customer: 'Alan Turing', date: '7 Oct 2026', total: '$86', status: 'Delivered' },
]

export function DashboardPage() {
  return (
    <>
      <div className="admin-header">
        <h1 className="admin-title">Dashboard</h1>
        <div className="admin-actions">
          <button type="button" className="btn btn-ghost">
            <Download size={18} aria-hidden />
            Export
          </button>
          <button type="button" className="btn btn-primary">
            <Plus size={18} aria-hidden />
            Add Product
          </button>
        </div>
      </div>
      <div className="kpi-grid">
        {kpis.map((kpi) => (
          <article key={kpi.label} className="kpi-card">
            <div className="kpi-label">
              <kpi.icon size={16} aria-hidden />
              {kpi.label}
            </div>
            <div className="kpi-value">{kpi.value}</div>
            <div className={`kpi-delta ${kpi.up ? 'up' : 'down'}`}>
              {kpi.up ? <TrendingUp size={14} aria-hidden /> : <TrendingDown size={14} aria-hidden />}
              {kpi.delta}
            </div>
          </article>
        ))}
      </div>
      <div className="table-container">
        <table className="data-table">
          <thead>
            <tr>
              <th>Order</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {recent.map((row) => (
              <tr key={row.id}>
                <td>{row.id}</td>
                <td>{row.customer}</td>
                <td>{row.date}</td>
                <td>{row.total}</td>
                <td>
                  <span className="status status-success">{row.status}</span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </>
  )
}
