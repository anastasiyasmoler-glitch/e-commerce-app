function Spinner({ label }: { label: string }) {
  return (
    <div
      data-slot="content-loader"
      className="flex flex-col items-center justify-center gap-3"
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

export function PageLoading({ label = 'Loading…' }: { label?: string }) {
  return (
    <div className="flex min-h-[calc(100vh-68px)] items-center justify-center bg-bg">
      <Spinner label={label} />
    </div>
  )
}

export function ContentLoader({ label = 'Loading…' }: { label?: string }) {
  return (
    <div className="flex min-h-[280px] items-center justify-center">
      <Spinner label={label} />
    </div>
  )
}
