import { http } from './http'

export type RecommendedProduct = {
  product_id: number
  store_id: number
  name: string
  slug: string
  price_cents: number | null
  score: number | null
  url: string | null
  image_url?: string | null
}

export type ExtractedFilters = {
  needs_clarification: boolean
  clarification_question: string | null
  search_query: string | null
  in_stock_only: boolean
  max_price_cents: number | null
  min_price_cents: number | null
  attributes: Record<string, unknown>
  store_id?: number
}

export type ChatResponse = {
  conversation_id: string
  answer: string
  extracted_filters: ExtractedFilters
  products: RecommendedProduct[]
}

export type ChatRequestBody = {
  message: string
  store_id: number
  conversation_id?: string | null
  limit?: number
}

export type ConversationMessage = {
  role: 'user' | 'assistant'
  content: string
  created_at: string
}

export type ConversationHistory = {
  conversation_id: string
  store_id: number
  expires_at: string
  last_activity_at: string
  messages: ConversationMessage[]
}

export async function sendChatMessage(body: ChatRequestBody): Promise<ChatResponse> {
  const { data } = await http.post<ChatResponse>('/api/recommendations/chat', body)
  return data
}

export async function fetchConversation(conversationId: string): Promise<ConversationHistory> {
  const { data } = await http.get<ConversationHistory>(
    `/api/recommendations/conversations/${conversationId}`,
  )
  return data
}

export async function deleteConversation(conversationId: string): Promise<void> {
  await http.delete(`/api/recommendations/conversations/${conversationId}`)
}
