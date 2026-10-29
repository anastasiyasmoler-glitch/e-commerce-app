export type CartLine = {
  productId: number
  storeId: number
  name: string
  slug: string
  priceCents: number
  quantity: number
  imageUrl?: string | null
}
