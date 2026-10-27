export const CHAT_MESSAGE_MAX_LENGTH = 2000

export const CHAT_SUGGESTED_PROMPTS = [
  'I want a pink sofa',
  'Minimal desk for a small room',
  'Dining table under 80000',
] as const

function conversationStorageKey(storeId: number): string {
  return `domo:chat:conversation:${storeId}`
}

export function readStoredConversationId(storeId: number): string | null {
  try {
    return sessionStorage.getItem(conversationStorageKey(storeId))
  } catch {
    return null
  }
}

export function writeStoredConversationId(storeId: number, conversationId: string | null): void {
  try {
    const key = conversationStorageKey(storeId)
    if (conversationId) {
      sessionStorage.setItem(key, conversationId)
    } else {
      sessionStorage.removeItem(key)
    }
  } catch {
    // ignore quota / private mode
  }
}
