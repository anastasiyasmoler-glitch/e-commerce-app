import { type FormEvent, type KeyboardEvent, useState } from 'react'
import toast from 'react-hot-toast'
import type { RecommendedProduct } from '@/api/recommendationApi'
import { apiErrorMessage } from '@/api/http'
import {
  CHAT_MESSAGE_MAX_LENGTH,
  CHAT_SUGGESTED_PROMPTS,
  readStoredConversationId,
  writeStoredConversationId,
} from '@/lib/chat/conversationStorage'
import { useEndChatConversation, useSendChatMessage } from '@/hooks/useRecommendationChat'

export type ChatTurn = {
  id: string
  role: 'user' | 'assistant'
  content: string
  products?: RecommendedProduct[]
}

export function useChatThread(storeId: number, storeName: string) {
  const [conversationId, setConversationId] = useState(() => readStoredConversationId(storeId))
  const [turns, setTurns] = useState<ChatTurn[]>([])
  const [draft, setDraft] = useState('')

  const sendChat = useSendChatMessage()
  const endChat = useEndChatConversation()

  const busy = sendChat.isPending

  async function submitMessage(rawMessage: string) {
    const message = rawMessage.trim()
    if (!message || busy) {
      return
    }
    if (message.length > CHAT_MESSAGE_MAX_LENGTH) {
      toast.error(`Message must be at most ${CHAT_MESSAGE_MAX_LENGTH} characters.`)
      return
    }

    setDraft('')

    const userTurn: ChatTurn = {
      id: crypto.randomUUID(),
      role: 'user',
      content: message,
    }
    setTurns((current) => [...current, userTurn])

    try {
      const response = await sendChat.mutateAsync({
        message,
        store_id: storeId,
        conversation_id: conversationId,
      })

      setConversationId(response.conversation_id)
      writeStoredConversationId(storeId, response.conversation_id)

      setTurns((current) => [
        ...current,
        {
          id: crypto.randomUUID(),
          role: 'assistant',
          content: response.answer,
          products: response.products.length > 0 ? response.products : undefined,
        },
      ])
    } catch (error) {
      setTurns((current) => current.filter((turn) => turn.id !== userTurn.id))
      setDraft(message)
      toast.error(apiErrorMessage(error))
    }
  }

  function onSubmit(event: FormEvent) {
    event.preventDefault()
    void submitMessage(draft)
  }

  function onComposerKeyDown(event: KeyboardEvent<HTMLTextAreaElement>) {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault()
      void submitMessage(draft)
    }
  }

  async function onClearConversation() {
    if (endChat.isPending) {
      return
    }

    const idToDelete = conversationId

    if (idToDelete) {
      try {
        await endChat.mutateAsync(idToDelete)
      } catch {
        // Local reset even if server already purged the context.
      }
    }

    setTurns([])
    setConversationId(null)
    writeStoredConversationId(storeId, null)
    toast.success('Conversation ended. You can start a new one.')
  }

  return {
    storeName,
    turns,
    draft,
    setDraft,
    busy,
    busyEnding: endChat.isPending,
    suggestedPrompts: CHAT_SUGGESTED_PROMPTS,
    messageMaxLength: CHAT_MESSAGE_MAX_LENGTH,
    showNewConversation: turns.length > 0 || conversationId !== null,
    submitMessage,
    onSubmit,
    onComposerKeyDown,
    onClearConversation,
  }
}
