export type CartLine = {
  productId: number
  storeId: number
  name: string
  slug: string
  sku: string
  priceCents: number
  quantity: number
  imageUrl?: string | null
}
