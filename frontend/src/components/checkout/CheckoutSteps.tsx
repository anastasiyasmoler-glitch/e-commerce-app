import { Check } from 'lucide-react'
import { Link } from 'react-router-dom'
import { paths } from '@/paths'

type CheckoutStepsProps = {
  active: 'cart' | 'payment' | 'confirmation'
}

export function CheckoutSteps({ active }: CheckoutStepsProps) {
  const cartDone = active !== 'cart'
  const paymentOn = active === 'payment'
  const paymentDone = active === 'confirmation'

  return (
    <div className="checkout-steps">
      <div className={`checkout-step ${cartDone ? 'done' : 'on'}`}>
        <span className="checkout-step-num">
          {cartDone ? <Check aria-hidden /> : '1'}
        </span>
        {cartDone ? (
          <Link to={paths.cart} className="hover:underline">
            Cart
          </Link>
        ) : (
          'Cart'
        )}
      </div>
      <div
        className={`checkout-step ${paymentDone ? 'done' : paymentOn ? 'on' : ''}`}
      >
        <span className="checkout-step-num">{paymentDone ? <Check aria-hidden /> : '2'}</span>
        Review &amp; payment
      </div>
      <div className={`checkout-step ${active === 'confirmation' ? 'on' : ''}`}>
        <span className="checkout-step-num">3</span>
        Confirmation
      </div>
    </div>
  )
}
