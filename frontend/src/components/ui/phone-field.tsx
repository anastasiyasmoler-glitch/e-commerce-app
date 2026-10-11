import type { ComponentProps } from 'react'
import PhoneInput, { type Value } from 'react-phone-number-input'
import 'react-phone-number-input/style.css'
import { cn } from '@/lib/utils'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

function PhoneTextInput(props: ComponentProps<'input'>) {
  return <Input {...props} className="h-11 border-0 px-2 shadow-none focus-visible:ring-0" />
}

type PhoneFieldProps = {
  id?: string
  label: string
  value: string
  onChange: (value: string) => void
  onBlur?: () => void
  name?: string
  error?: string
}

export function PhoneField({ id, label, value, onChange, onBlur, name, error }: PhoneFieldProps) {
  return (
    <div className="mb-4 grid gap-1.5">
      <Label htmlFor={id}>{label}</Label>
      <PhoneInput
        id={id}
        name={name}
        international
        defaultCountry="BY"
        value={(value || undefined) as Value}
        onChange={(next) => onChange(next ?? '')}
        onBlur={onBlur}
        inputComponent={PhoneTextInput}
        aria-invalid={Boolean(error)}
        className={cn('PhoneInput', error && 'PhoneInput--invalid')}
      />
    </div>
  )
}
