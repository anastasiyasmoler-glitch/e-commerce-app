import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { isAxiosError } from 'axios'
import {
  deleteConversation,
  fetchConversation,
  recommendationKeys,
  sendChatMessage,
  type ChatRequestBody,
} from '@/api/recommendationApi'

export function useConversationHistory(conversationId: string | null) {
  return useQuery({
    queryKey: recommendationKeys.conversation(conversationId ?? ''),
    queryFn: () => fetchConversation(conversationId!),
    enabled: conversationId !== null && conversationId !== '',
    retry: (failureCount, error) => {
      if (isAxiosError(error) && error.response?.status === 404) {
        return false
      }

      return failureCount < 1
    },
  })
}

export function useSendChatMessage() {
  return useMutation({
    mutationFn: (body: ChatRequestBody) => sendChatMessage(body),
  })
}

export function useEndChatConversation() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (conversationId: string) => deleteConversation(conversationId),
    onSuccess: (_data, conversationId) => {
      queryClient.removeQueries({ queryKey: recommendationKeys.conversation(conversationId) })
    },
  })
}
