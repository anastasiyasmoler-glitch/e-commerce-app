import { AlertCircle, CheckCircle2, Clock } from 'lucide-react'

type OrderConfirmationBannerProps = {
  tone: 'ok' | 'pending' | 'bad'
  title: string
  body: string
  email?: string
}

export function OrderConfirmationBanner({ tone, title, body, email }: OrderConfirmationBannerProps) {
  const Icon = tone === 'ok' ? CheckCircle2 : tone === 'bad' ? AlertCircle : Clock

  const detail =
    email && tone !== 'bad'
      ? `${body}${body.endsWith('.') ? '' : '.'} We will email you at ${email}.`
      : body

  return (
    <div className={`order-banner ${tone}`}>
      <Icon aria-hidden className="size-7 shrink-0" strokeWidth={1.75} />
      <div>
        <h1 className="font-display text-[24px] font-medium leading-snug">{title}</h1>
        <p className="mt-1 text-[15px]">{detail}</p>
      </div>
    </div>
  )
}
