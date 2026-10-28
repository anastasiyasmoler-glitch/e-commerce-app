import { Link, Navigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { Field } from '@/components/ui/field'
import { FormError } from '@/components/ui/form-error'
import { useCheckoutPage } from '@/hooks/useCheckoutPage'
import { formatUsd } from '@/lib/money/formatUsd'
import { firstFormError } from '@/lib/form'
import { paths } from '@/paths'

const checkoutSchema = z.object({
  line1: z.string().min(3, 'Enter a street address'),
  city: z.string().min(2, 'Enter a city'),
  postal_code: z.string().min(3, 'Enter a postal code'),
  country: z.string().length(2, 'Use a 2-letter country code'),
})

type CheckoutForm = z.infer<typeof checkoutSchema>

export function CheckoutPage() {
  const checkout = useCheckoutPage()
  const form = useForm<CheckoutForm>({
    resolver: zodResolver(checkoutSchema),
    defaultValues: { line1: '', city: '', postal_code: '', country: 'US' },
  })

  if (checkout.isEmpty) {
    return <Navigate to={paths.cart} replace />
  }

  return (
    <Container className="py-8">
      <h1 className="mb-8 font-display text-[36px] font-medium">Checkout</h1>
      <div className="grid gap-10 lg:grid-cols-[1fr_320px]">
        <form
          className="rounded-[8px] border border-line bg-surface p-6"
          onSubmit={form.handleSubmit((values) => checkout.placeOrder.mutate(values))}
        >
          <h2 className="text-[18px] font-semibold">Shipping address</h2>
          <FormError message={firstFormError(form.formState.errors)} />
          <div className="mt-4 space-y-4">
            <Field
              id="checkout-line1"
              label="Address line"
              autoComplete="street-address"
              error={form.formState.errors.line1?.message}
              {...form.register('line1')}
            />
            <div className="grid gap-4 sm:grid-cols-2">
              <Field
                id="checkout-city"
                label="City"
                autoComplete="address-level2"
                error={form.formState.errors.city?.message}
                {...form.register('city')}
              />
              <Field
                id="checkout-postal"
                label="Postal code"
                autoComplete="postal-code"
                error={form.formState.errors.postal_code?.message}
                {...form.register('postal_code')}
              />
            </div>
            <Field
              id="checkout-country"
              label="Country code"
              autoComplete="country"
              placeholder="US"
              error={form.formState.errors.country?.message}
              {...form.register('country')}
            />
          </div>
          <div className="mt-6 flex flex-wrap gap-3">
            <Button
              type="submit"
              className="h-11 rounded-sm"
              disabled={checkout.placeOrder.isPending}
            >
              {checkout.placeOrder.isPending ? 'Placing order…' : 'Place order'}
            </Button>
            <Button type="button" variant="outline" className="h-11 rounded-sm" asChild>
              <Link to={paths.cart}>Back to cart</Link>
            </Button>
          </div>
        </form>
        <aside className="h-fit rounded-[8px] border border-line bg-surface p-6">
          <h2 className="text-[13px] font-semibold tracking-[0.08em] uppercase">Order summary</h2>
          <ul className="mt-4 space-y-2 text-[14px]">
            {checkout.lines.map((line) => (
              <li key={`${line.storeId}:${line.productId}`} className="flex justify-between gap-4">
                <span className="text-muted">
                  {line.name} × {line.quantity}
                </span>
                <span>{formatUsd(line.priceCents * line.quantity)}</span>
              </li>
            ))}
          </ul>
          <div className="mt-4 flex justify-between border-t border-line pt-4 font-semibold">
            <span>Total</span>
            <span>{formatUsd(checkout.subtotalCents)}</span>
          </div>
        </aside>
      </div>
    </Container>
  )
}
