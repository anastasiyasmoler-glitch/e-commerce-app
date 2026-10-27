import { Loader2, SendHorizontal, Sparkles } from 'lucide-react'
import { ChatBotAvatar } from '@/components/chat/ChatBotAvatar'
import { ChatMessageRow } from '@/components/chat/ChatMessageRow'
import { ChatMessengerHeader } from '@/components/chat/ChatMessengerHeader'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { PageLoading } from '@/components/ui/loading-screen'
import { useChatPage } from '@/hooks/useChatPage'
import { useChatThread } from '@/hooks/useChatThread'

function ChatThreadView({ storeId, storeName }: { storeId: number; storeName: string }) {
  const chat = useChatThread(storeId, storeName)

  return (
    <section
      className="flex h-[calc(100dvh-116px)] min-h-[480px] flex-col bg-surface"
      aria-label="Chat with AI assistant"
    >
      <ChatMessengerHeader
        storeName={chat.storeName}
        showNewConversation={chat.showNewConversation}
        busyEnding={chat.busyEnding}
        onNewConversation={() => void chat.onClearConversation()}
      />

      <div
        ref={chat.scrollRef}
        className="flex-1 overflow-y-auto bg-bg px-6 py-8 sm:px-10"
      >
        <div className="mx-auto flex min-h-full max-w-[880px] flex-col gap-5">
          {chat.turns.length === 0 && !chat.busy ? (
            <div className="m-auto flex max-w-[440px] flex-col items-center py-8 text-center">
              <span className="text-[12px] font-semibold tracking-[0.12em] text-muted uppercase">
                Catalog assistant
              </span>
              <h2 className="mt-3 font-display text-[32px] leading-tight font-medium tracking-[-0.02em] text-ink sm:text-[36px]">
                Describe it, we&apos;ll find it
              </h2>
              <p className="mt-3 text-[15px] text-muted">
                Suggestions come only from {chat.storeName} — nothing invented.
              </p>
              <div className="mt-8 flex flex-wrap justify-center gap-2">
                {chat.suggestedPrompts.map((prompt) => (
                  <Button
                    key={prompt}
                    type="button"
                    variant="outline"
                    className="h-9 rounded-[6px] border-line bg-surface px-4 text-[13px] font-medium text-ink hover:bg-brand-soft hover:text-brand"
                    onClick={() => void chat.submitMessage(prompt)}
                  >
                    {prompt}
                  </Button>
                ))}
              </div>
            </div>
          ) : null}

          {chat.turns.map((turn) => (
            <ChatMessageRow key={turn.id} turn={turn} storeName={chat.storeName} />
          ))}

          {chat.busy ? (
            <div className="flex gap-2.5">
              <ChatBotAvatar />
              <p className="inline-flex items-center gap-2 rounded-[8px] bg-subtle px-4 py-3 text-[15px] text-muted">
                <Loader2 className="size-4 animate-spin" aria-hidden />
                Finding matches in the catalog…
              </p>
            </div>
          ) : null}
        </div>
      </div>

      <div className="shrink-0 border-t border-line bg-surface">
        <form
          onSubmit={chat.onSubmit}
          className="mx-auto flex max-w-[880px] items-end gap-3 px-6 py-4 sm:px-10 sm:py-5"
        >
          <label className="sr-only" htmlFor="chat-message">
            Message
          </label>
          <textarea
            id="chat-message"
            value={chat.draft}
            onChange={(event) => chat.setDraft(event.target.value)}
            onKeyDown={chat.onComposerKeyDown}
            rows={1}
            maxLength={chat.messageMaxLength}
            disabled={chat.busy}
            placeholder="Ask about furniture, size, colour, budget…"
            className="max-h-32 min-h-[52px] flex-1 resize-none rounded-[6px] border border-line bg-bg px-4 py-3 text-[15px] text-ink outline-none transition-colors placeholder:text-muted/70 focus:border-brand focus:ring-3 focus:ring-brand/14 disabled:opacity-60"
          />
          <Button
            type="submit"
            disabled={chat.busy || chat.draft.trim() === ''}
            className="h-[52px] shrink-0 rounded-[6px] bg-brand px-5 text-[15px] font-semibold text-white hover:bg-brand-dark disabled:opacity-50"
          >
            {chat.busy ? (
              <Loader2 className="size-5 animate-spin" />
            ) : (
              <>
                Send
                <SendHorizontal className="size-4" />
              </>
            )}
          </Button>
        </form>
      </div>
    </section>
  )
}

export function ChatPage() {
  const page = useChatPage()

  if (page.status === 'loading') {
    return <PageLoading />
  }

  if (page.status === 'no-store') {
    return (
      <Container className="py-20 text-center">
        <Sparkles className="mx-auto size-8 text-brand" aria-hidden />
        <h1 className="mt-4 font-display text-[28px] font-medium">AI Assistant</h1>
        <p className="mt-2 text-[15px] text-muted">Select a store in the header to start chatting.</p>
      </Container>
    )
  }

  return (
    <div className="flex min-h-0 flex-1 flex-col" data-slot="chat-page">
      <ChatThreadView key={page.storeId} storeId={page.storeId!} storeName={page.storeName} />
    </div>
  )
}
