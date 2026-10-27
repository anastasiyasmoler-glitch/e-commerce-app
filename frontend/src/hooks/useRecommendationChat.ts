import { useMutation } from '@tanstack/react-query'
import { deleteConversation, sendChatMessage, type ChatRequestBody } from '@/api/recommendationApi'

export function useSendChatMessage() {
  return useMutation({
    mutationFn: (body: ChatRequestBody) => sendChatMessage(body),
  })
}

export function useEndChatConversation() {
  return useMutation({
    mutationFn: (conversationId: string) => deleteConversation(conversationId),
  })
}
