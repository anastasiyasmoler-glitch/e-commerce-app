export function LoadingScreen({ label = 'Loading…' }: { label?: string }) {
  return (
    <div className="loading-screen" role="status" aria-live="polite">
      <div className="loading-logo">
        Domo<i>.</i>
      </div>
      <span className="spinner" aria-hidden />
      <div className="muted">{label}</div>
    </div>
  )
}
