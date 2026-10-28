import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useState,
  type ReactNode,
} from 'react'
import type { CartLine } from './types'

type CartContextValue = {
  lines: CartLine[]
  itemCount: number
  subtotalCents: number
  addLine: (line: Omit<CartLine, 'quantity'>, quantity?: number) => void
  setQuantity: (productId: number, storeId: number, quantity: number) => void
  removeLine: (productId: number, storeId: number) => void
  clear: () => void
}

const CartContext = createContext<CartContextValue | null>(null)

function lineKey(productId: number, storeId: number): string {
  return `${storeId}:${productId}`
}

export function CartProvider({ children }: { children: ReactNode }) {
  const [lines, setLines] = useState<CartLine[]>([])

  const addLine = useCallback((line: Omit<CartLine, 'quantity'>, quantity = 1) => {
    const safeQty = Math.max(1, quantity)
    setLines((current) => {
      const key = lineKey(line.productId, line.storeId)
      const existing = current.find(
        (item) => lineKey(item.productId, item.storeId) === key,
      )
      if (existing) {
        return current.map((item) =>
          lineKey(item.productId, item.storeId) === key
            ? { ...item, quantity: item.quantity + safeQty }
            : item,
        )
      }
      return [...current, { ...line, quantity: safeQty }]
    })
  }, [])

  const setQuantity = useCallback((productId: number, storeId: number, quantity: number) => {
    const key = lineKey(productId, storeId)
    if (quantity < 1) {
      setLines((current) => current.filter((item) => lineKey(item.productId, item.storeId) !== key))
      return
    }
    setLines((current) =>
      current.map((item) =>
        lineKey(item.productId, item.storeId) === key ? { ...item, quantity } : item,
      ),
    )
  }, [])

  const removeLine = useCallback((productId: number, storeId: number) => {
    const key = lineKey(productId, storeId)
    setLines((current) => current.filter((item) => lineKey(item.productId, item.storeId) !== key))
  }, [])

  const clear = useCallback(() => setLines([]), [])

  const value = useMemo(() => {
    const itemCount = lines.reduce((sum, line) => sum + line.quantity, 0)
    const subtotalCents = lines.reduce((sum, line) => sum + line.priceCents * line.quantity, 0)
    return { lines, itemCount, subtotalCents, addLine, setQuantity, removeLine, clear }
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
