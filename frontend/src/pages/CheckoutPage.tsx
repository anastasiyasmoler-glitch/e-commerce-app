import { Link } from 'react-router-dom'
import { AlertCircle, Lock, Truck } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { ProductImage } from '@/components/catalog/ProductImage'
import { CheckoutPaymentPanel } from '@/components/checkout/CheckoutPaymentPanel'
import { CheckoutSteps } from '@/components/checkout/CheckoutSteps'
import { useCheckoutPage } from '@/hooks/useCheckoutPage'
import { formatOrderMoney } from '@/lib/order/orderStatus'
import { paths } from '@/paths'

export function CheckoutPage() {
  const checkout = useCheckoutPage()

  if (checkout.needsAuth) {
    return (
      <Container className="py-16 text-center">
        <h1 className="font-display text-[32px] font-medium">Checkout</h1>
        <p className="mt-4 text-muted">Sign in to place an order.</p>
        <Button asChild className="mt-6 rounded-[6px] bg-brand text-white hover:bg-brand-dark">
          <Link to={paths.profile}>Go to profile</Link>
        </Button>
      </Container>
    )
  }

  if (checkout.lines.length === 0 && checkout.payOrderId === null) {
    return (
      <Container className="py-16 text-center">
        <h1 className="font-display text-[32px] font-medium">Checkout</h1>
        <p className="mt-4 text-muted">Your cart is empty.</p>
        <Button asChild className="mt-6 rounded-[6px] bg-brand text-white hover:bg-brand-dark">
          <Link to={paths.cart}>View cart</Link>
        </Button>
      </Container>
    )
  }

  const totalCents = checkout.lines.reduce(
    (sum, line) => sum + line.priceCents * line.quantity,
    0,
  )
  const currency = 'USD'
  const profile = checkout.profile
  const showPaymentForm = checkout.payOrderId !== null

  return (
    <Container className="py-8 pb-16">
      <CheckoutSteps active="payment" />

      <h1 className="mb-8 text-left font-display text-[36px] font-medium tracking-[-0.02em]">
        Review &amp; payment
      </h1>

      {checkout.paymentCanceled ? (
        <div className="checkout-notice mb-6" role="status">
          <AlertCircle aria-hidden className="size-[18px] shrink-0 text-brand" />
          <div className="flex min-w-0 flex-1 items-start justify-between gap-4">
            <p className="text-[13px] leading-relaxed">
              Payment was not completed. Your order may still be awaiting payment — try again below.
            </p>
            <button
              type="button"
              className="shrink-0 text-[12px] font-medium text-brand hover:underline"
              onClick={() => checkout.dismissPaymentNotice()}
            >
              Dismiss
            </button>
          </div>
        </div>
      ) : null}

      {checkout.checkoutError ? (
        <div className="mb-6 rounded-[6px] border border-danger bg-danger-soft px-4 py-3 text-[13px] leading-relaxed text-danger" role="alert">
          <p className="font-semibold">Checkout could not start</p>
          <p className="mt-1">{checkout.checkoutError}</p>
          <p className="mt-2 text-ink">
            If a product is not available, it is missing from Order snapshots — remove it from the{' '}
            <Link to={paths.cart} className="underline">
              cart
            </Link>{' '}
            or wait for catalog events to sync.
          </p>
        </div>
      ) : null}

      <div className="grid gap-10 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div className="flex flex-col gap-6">
          <section className="checkout-panel">
            <h2 className="checkout-panel-title">Account</h2>
            {checkout.profilePending || !profile ? (
              <p className="text-[13px] text-muted">Loading profile…</p>
            ) : (
              <>
                <div className="checkout-info-row">
                  <span>Name</span>
                  <span>{profile.name}</span>
                </div>
                <div className="checkout-info-row">
                  <span>Email</span>
                  <span>{profile.email}</span>
                </div>
                <div className="checkout-info-row">
                  <span>Phone</span>
                  <span>{profile.phone || '—'}</span>
                </div>
                <p className="mt-3 text-[13px] text-muted">Order updates are sent to this email.</p>
              </>
            )}
          </section>

          <section className="checkout-panel">
            <h2 className="checkout-panel-title">Delivery</h2>
            <div className="checkout-shipping-option">
              <Truck aria-hidden className="size-5 shrink-0 text-brand" />
              <div>
                <p>Standard delivery</p>
                <span>3–5 business days · Free</span>
              </div>
            </div>
          </section>

          {showPaymentForm && checkout.payOrderId !== null ? (
            <CheckoutPaymentPanel orderId={checkout.payOrderId} />
          ) : (
            <section className="checkout-panel">
              <h2 className="checkout-panel-title">Payment</h2>
              <p className="text-[13px] text-muted">
                Place the order to reserve stock, then enter your card on this page.
              </p>
            </section>
          )}
        </div>

        <aside className="checkout-summary sticky top-24 h-fit">
          <h2 className="font-display text-[20px] font-medium">Your order</h2>
          <ul className="mt-4">
            {checkout.lines.map((line) => (
              <li key={line.productId} className="checkout-summary-item">
                <ProductImage
                  name={line.name}
                  slug={line.slug}
                  imageUrl={line.imageUrl}
                  className="size-12 shrink-0 rounded-[6px] object-cover"
                />
                <span className="name">{line.name}</span>
                <span className="muted">×{line.quantity}</span>
                <b>{formatOrderMoney(line.priceCents * line.quantity, currency)}</b>
              </li>
            ))}
          </ul>
          <div className="checkout-summary-total">
            <span>Total</span>
            <span>{formatOrderMoney(totalCents, currency)}</span>
          </div>
          {!showPaymentForm ? (
            <>
              <Button
                type="button"
                className="mt-6 h-12 w-full rounded-[6px] bg-brand text-[16px] font-semibold text-white hover:bg-brand-dark"
                disabled={!checkout.canCheckout || checkout.isBusy}
                onClick={() => checkout.startCheckout()}
              >
                <Lock aria-hidden className="size-[18px]" />
                {checkout.isBusy
                  ? 'Placing order…'
                  : `Place order · ${formatOrderMoney(totalCents, currency)}`}
              </Button>
              <p className="checkout-secure-note">
                <Lock aria-hidden className="size-[14px]" />
                Payments are processed by Stripe
              </p>
            </>
          ) : null}
        </aside>
      </div>
    </Container>
  )
}
