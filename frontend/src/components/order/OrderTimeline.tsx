import { Check } from 'lucide-react'
import type { TimelineStepState } from '@/lib/order/orderTimeline'

type OrderTimelineProps = {
  steps: Array<{
    id: string
    title: string
    description: string
    state: TimelineStepState
  }>
}

function dotContent(state: TimelineStepState) {
  if (state === 'done') {
    return <Check aria-hidden className="size-3" strokeWidth={3} />
  }

  return null
}

export function OrderTimeline({ steps }: OrderTimelineProps) {
  return (
    <div className="order-timeline">
      {steps.map((step) => (
        <div key={step.id} className={`order-timeline-item ${step.state}`}>
          <div className="order-timeline-dot">{dotContent(step.state)}</div>
          <div className="order-timeline-body">
            <b>{step.title}</b>
            <span>{step.description}</span>
          </div>
        </div>
      ))}
    </div>
  )
}
