import { cn } from '@/lib/utils'
import type { ComponentProps } from 'react'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

type FieldProps = ComponentProps<typeof Input> & {
  label: string
  error?: string
}

export function Field({ id, label, error, className, ...props }: FieldProps) {
  return (
    <div className="mb-4 grid gap-1.5">
      <Label htmlFor={id}>{label}</Label>
      <Input id={id} aria-invalid={Boolean(error)} className={cn(className)} {...props} />
    </div>
  )
}
