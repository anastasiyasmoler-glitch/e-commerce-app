import { Elements, PaymentElement, useElements, useStripe } from '@stripe/react-stripe-js'
import { loadStripe, type Stripe } from '@stripe/stripe-js'
import { useMutation, useQuery } from '@tanstack/react-query'
import { Lock } from 'lucide-react'
import { useMemo, useState } from 'react'
import toast from 'react-hot-toast'
import { getOrderPaymentCheckout } from '@/api/orderApi'
import { apiErrorMessage } from '@/api/http'
import { Button } from '@/components/ui/button'
import { orderConfirmationPath } from '@/paths'

type CheckoutPaymentPanelProps = {
  orderId: number
}

function PaymentForm({ orderId }: CheckoutPaymentPanelProps) {
  const stripe = useStripe()
  const elements = useElements()
  const [submitting, setSubmitting] = useState(false)

  const pay = useMutation({
    mutationFn: async () => {
      if (!stripe || !elements) {
        throw new Error('Stripe is still loading.')
      }

      setSubmitting(true)

      const returnUrl = `${window.location.origin}${orderConfirmationPath(orderId)}`

      const result = await stripe.confirmPayment({
        elements,
        confirmParams: { return_url: returnUrl },
      })

      if (result.error) {
        throw new Error(result.error.message ?? 'Payment failed.')
      }
    },
    onError: (error) => {
      setSubmitting(false)
      toast.error(apiErrorMessage(error))
    },
  })

  return (
    <>
      <PaymentElement />
      <Button
        type="button"
        className="mt-6 h-12 w-full rounded-[6px] bg-brand text-[16px] font-semibold text-white hover:bg-brand-dark"
        disabled={!stripe || !elements || submitting || pay.isPending}
        onClick={() => pay.mutate()}
      >
        <Lock aria-hidden className="size-[18px]" />
        {submitting || pay.isPending ? 'Processing…' : 'Pay now'}
      </Button>
    </>
  )
}

export function CheckoutPaymentPanel({ orderId }: CheckoutPaymentPanelProps) {
  const paymentQuery = useQuery({
    queryKey: ['orders', 'payment', orderId],
    queryFn: () => getOrderPaymentCheckout(orderId),
  })

  const stripePromise = useMemo((): Promise<Stripe | null> | null => {
    const key = paymentQuery.data?.publishable_key
    if (!key) {
      return null
    }
    return loadStripe(key)
  }, [paymentQuery.data?.publishable_key])

  return (
    <section className="checkout-panel">
      <h2 className="checkout-panel-title">Payment</h2>
      {paymentQuery.isPending ? (
        <p className="text-[13px] text-muted">Loading payment form…</p>
      ) : null}
      {paymentQuery.isError ? (
        <p className="text-[13px] text-danger" role="alert">
          {apiErrorMessage(paymentQuery.error)}
        </p>
      ) : null}
      {paymentQuery.isSuccess && stripePromise && paymentQuery.data.client_secret ? (
        <Elements
          stripe={stripePromise}
          options={{ clientSecret: paymentQuery.data.client_secret }}
        >
          <div className="checkout-notice mb-4">
            <Lock aria-hidden className="size-[18px] shrink-0" />
            <p className="text-[13px] leading-relaxed">
              Card details are processed by Stripe on this page. Test card:{' '}
              <strong>4242 4242 4242 4242</strong>, any future date, any CVC.
            </p>
          </div>
          <PaymentForm orderId={orderId} />
        </Elements>
      ) : null}
    </section>
  )
}
