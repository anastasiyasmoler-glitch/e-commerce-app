import { Bot } from 'lucide-react'

type ChatBotAvatarProps = {
  online?: boolean
  size?: 'sm' | 'md'
}

export function ChatBotAvatar({ online = false, size = 'sm' }: ChatBotAvatarProps) {
  const box = size === 'md' ? 'size-9' : 'size-8'
  const icon = size === 'md' ? 'size-5' : 'size-4'

  return (
    <span className={`relative inline-grid ${box} shrink-0 place-items-center`}>
      <span className={`grid ${box} place-items-center rounded-full bg-brand-soft text-brand`} aria-hidden>
        <Bot className={icon} strokeWidth={1.75} />
      </span>
      {online ? (
        <span
          className="absolute right-0 bottom-0 size-2.5 rounded-full border-2 border-surface bg-success"
          title="Online"
          aria-label="Online"
        />
      ) : null}
    </span>
  )
}
