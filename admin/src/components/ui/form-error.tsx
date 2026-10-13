import { CircleAlert } from 'lucide-react'
import { cn } from '@/lib/utils'

export function FormError({ message, className }: { message?: string | null; className?: string }) {
  return (
    <div
      role="alert"
      className={cn(
        'grid overflow-hidden transition-[grid-template-rows,opacity] duration-200 ease-out',
        message ? 'grid-rows-[1fr] opacity-100' : 'pointer-events-none grid-rows-[0fr] opacity-0',
        className,
      )}
    >
      <div className="min-h-0">
        <p className="flex items-start gap-2 rounded-sm border border-danger/40 bg-danger-soft px-3 py-2 text-[13px] leading-5 text-danger">
          <CircleAlert className="mt-0.5 size-4 shrink-0" aria-hidden />
          <span>{message ?? '\u00a0'}</span>
        </p>
      </div>
    </div>
  )
}
