import { useState } from 'react'
import { MessageSquarePlus } from 'lucide-react'
import { ChatBotAvatar } from '@/components/chat/ChatBotAvatar'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

type ChatMessengerHeaderProps = {
  storeName: string
  showNewConversation: boolean
  busyEnding: boolean
  messageCount: number
  onNewConversation: () => void | Promise<void>
}

export function ChatMessengerHeader({
  storeName,
  showNewConversation,
  busyEnding,
  messageCount,
  onNewConversation,
}: ChatMessengerHeaderProps) {
  const [confirmOpen, setConfirmOpen] = useState(false)

  function handleNewClick() {
    if (messageCount > 0) {
      setConfirmOpen(true)
      return
    }
    void onNewConversation()
  }

  async function handleConfirmEnd() {
    await onNewConversation()
    setConfirmOpen(false)
  }

  return (
    <>
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
            variant="outline"
            className="h-8 shrink-0 gap-1.5 rounded-[6px] border-line px-3 text-[12px] font-semibold text-ink"
            onClick={handleNewClick}
            disabled={busyEnding}
          >
            <MessageSquarePlus className="size-3.5" aria-hidden />
            {busyEnding ? 'Ending…' : 'New chat'}
          </Button>
        ) : null}
      </header>

      <Dialog open={confirmOpen} onOpenChange={setConfirmOpen}>
        <DialogContent className="max-w-[400px] rounded-[10px]">
          <DialogHeader>
            <DialogTitle className="font-display text-[22px] font-medium">End this chat?</DialogTitle>
            <DialogDescription className="text-[14px] text-muted">
              Messages in this conversation will be deleted on the server. You can start a fresh chat
              right away.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter className="gap-2 sm:gap-2">
            <Button type="button" variant="outline" onClick={() => setConfirmOpen(false)}>
              Cancel
            </Button>
            <Button
              type="button"
              className="bg-brand text-white hover:bg-brand-dark"
              disabled={busyEnding}
              onClick={() => void handleConfirmEnd()}
            >
              {busyEnding ? 'Ending…' : 'End & start new'}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </>
  )
}
