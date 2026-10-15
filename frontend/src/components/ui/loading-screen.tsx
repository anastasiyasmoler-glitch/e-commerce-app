export function LoadingScreen({ label = 'Loading…' }: { label?: string }) {
  return (
    <div
      className="flex min-h-screen flex-col items-center justify-center gap-4 bg-bg"
      role="status"
      aria-live="polite"
    >
      <div className="font-display text-[32px] font-medium tracking-tight text-ink">
        Domo<span className="text-brand">.</span>
      </div>
      <span
        className="size-7 animate-spin rounded-full border-[3px] border-line border-t-brand"
        aria-hidden
      />
      <div className="text-[14px] text-muted">{label}</div>
    </div>
  )
}

export function PageLoading({ label = 'Loading…' }: { label?: string }) {
  return (
    <div
      data-slot="page-loading"
      className="flex min-h-[calc(100vh-68px)] flex-col items-center justify-center gap-4 bg-bg"
      role="status"
      aria-live="polite"
    >
      <span
        className="size-7 animate-spin rounded-full border-[3px] border-line border-t-brand"
        aria-hidden
      />
      <div className="text-[14px] text-muted">{label}</div>
    </div>
  )
}
