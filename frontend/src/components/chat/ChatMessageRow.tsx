import { ChatBotAvatar } from '@/components/chat/ChatBotAvatar'
import { ChatRecommendedProduct } from '@/components/chat/ChatRecommendedProduct'
import type { ChatTurn } from '@/hooks/useChatThread'

type ChatMessageRowProps = {
  turn: ChatTurn
  storeName: string
}

export function ChatMessageRow({ turn, storeName }: ChatMessageRowProps) {
  const isUser = turn.role === 'user'

  return (
    <div className={`flex gap-2.5 ${isUser ? 'flex-row-reverse' : 'flex-row'}`}>
      {isUser ? <span className="size-8 shrink-0" aria-hidden /> : <ChatBotAvatar />}
      <div
        className={`flex min-w-0 max-w-[720px] flex-1 flex-col gap-3 ${isUser ? 'items-end' : 'items-start'}`}
      >
        <p
          className={`max-w-full px-4 py-3 text-[15px] leading-relaxed ${
            isUser ? 'rounded-[8px] bg-brand text-white' : 'rounded-[8px] bg-subtle text-ink'
          }`}
        >
          {turn.content}
        </p>
        {turn.products && turn.products.length > 0 ? (
          <div className="grid w-full gap-3 sm:grid-cols-2">
            {turn.products.map((product) => (
              <ChatRecommendedProduct
                key={product.product_id}
                product={product}
                storeName={storeName}
              />
            ))}
          </div>
        ) : null}
      </div>
    </div>
  )
}
