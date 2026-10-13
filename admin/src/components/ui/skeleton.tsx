import type { ComponentProps } from 'react'
import { cn } from '@/lib/utils'
import { Label } from '@/components/ui/label'

export function Skeleton({ className, ...props }: ComponentProps<'div'>) {
  return (
    <div
      data-slot="skeleton"
      className={cn('animate-pulse rounded-sm bg-subtle', className)}
      {...props}
    />
  )
}

export function FieldSkeleton({ label }: { label: string }) {
  return (
    <div className="mb-4 grid gap-1.5">
      <Label>{label}</Label>
      <Skeleton className="h-11 w-full" />
    </div>
  )
}
