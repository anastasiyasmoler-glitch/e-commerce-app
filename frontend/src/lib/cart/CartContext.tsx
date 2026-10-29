import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useState,
  type ReactNode,
} from 'react'
import type { CartLine } from '@/lib/cart/types'

type CartContextValue = {
  lines: CartLine[]
  itemCount: number
  subtotalCents: number
  addLine: (line: Omit<CartLine, 'quantity'>, quantity?: number) => void
  setQuantity: (productId: number, quantity: number) => void
  removeLine: (productId: number) => void
  clear: () => void
}

const CartContext = createContext<CartContextValue | null>(null)

export function CartProvider({ children }: { children: ReactNode }) {
  const [lines, setLines] = useState<CartLine[]>([])

  const addLine = useCallback((line: Omit<CartLine, 'quantity'>, quantity = 1) => {
    const qty = Math.max(1, quantity)
    setLines((current) => {
      const existing = current.find((entry) => entry.productId === line.productId)
      if (existing) {
        return current.map((entry) =>
          entry.productId === line.productId
            ? { ...entry, quantity: entry.quantity + qty }
            : entry,
        )
      }

      return [...current, { ...line, quantity: qty }]
    })
  }, [])

  const setQuantity = useCallback((productId: number, quantity: number) => {
    setLines((current) => {
      if (quantity < 1) {
        return current.filter((entry) => entry.productId !== productId)
      }

      return current.map((entry) =>
        entry.productId === productId ? { ...entry, quantity } : entry,
      )
    })
  }, [])

  const removeLine = useCallback((productId: number) => {
    setLines((current) => current.filter((entry) => entry.productId !== productId))
  }, [])

  const clear = useCallback(() => setLines([]), [])

  const value = useMemo((): CartContextValue => {
    const itemCount = lines.reduce((sum, line) => sum + line.quantity, 0)
    const subtotalCents = lines.reduce(
      (sum, line) => sum + line.priceCents * line.quantity,
      0,
    )

    return {
      lines,
      itemCount,
      subtotalCents,
      addLine,
      setQuantity,
      removeLine,
      clear,
    }
  }, [lines, addLine, setQuantity, removeLine, clear])

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>
}

export function useCart(): CartContextValue {
  const context = useContext(CartContext)
  if (!context) {
    throw new Error('useCart must be used within CartProvider')
  }

  return context
}
