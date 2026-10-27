import { ChatBotAvatar } from '@/components/chat/ChatBotAvatar'
import { Button } from '@/components/ui/button'

type ChatMessengerHeaderProps = {
  storeName: string
  showNewConversation: boolean
  busyEnding: boolean
  onNewConversation: () => void
}

export function ChatMessengerHeader({
  storeName,
  showNewConversation,
  busyEnding,
  onNewConversation,
}: ChatMessengerHeaderProps) {
  return (
    <header
      className="flex min-h-[52px] shrink-0 items-center gap-3 border-b border-line bg-surface px-6"
      aria-label="Chat header"
    >
      <ChatBotAvatar online size="md" />
      <div className="min-w-0 flex-1">
        <p className="truncate text-[13px] font-semibold leading-tight text-ink">Assistant</p>
        <p className="truncate text-[12px] text-muted">
          <span className="text-success">Online</span>
          <span aria-hidden> · </span>
          {storeName}
        </p>
      </div>
      {showNewConversation ? (
        <Button
          type="button"
          variant="ghost"
          className="h-8 shrink-0 px-2 text-[13px] font-semibold text-muted hover:text-brand"
          onClick={onNewConversation}
          disabled={busyEnding}
        >
          New conversation
        </Button>
      ) : null}
    </header>
  )
}
