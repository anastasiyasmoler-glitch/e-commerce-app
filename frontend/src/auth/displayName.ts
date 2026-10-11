const KEY = 'domo.displayName'

export function readDisplayName(): string | null {
  try {
    return sessionStorage.getItem(KEY)
  } catch {
    return null
  }
}

export function writeDisplayName(name: string): void {
  try {
    sessionStorage.setItem(KEY, name)
  } catch {
    /* ignore quota / private mode */
  }
}

export function clearDisplayName(): void {
  try {
    sessionStorage.removeItem(KEY)
  } catch {
    /* ignore */
  }
}
