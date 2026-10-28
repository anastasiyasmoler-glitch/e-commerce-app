import toast from 'react-hot-toast'
import { useParams } from 'react-router-dom'
import { useSelectedStore } from '@/catalog/useSelectedStore'
import { useCart } from '@/lib/cart/CartContext'
import { productImagePublicUrl } from '@/lib/catalog/productImageUrl'
import { useCategories } from '@/hooks/useCategories'
import { useProduct } from '@/hooks/useProduct'

export type ProductPageStatus = 'loading' | 'not-found' | 'ready'

export function useProductPage() {
  const { id } = useParams()
  const productId = Number(id)
  const validId = Number.isFinite(productId) ? productId : undefined
  const { selectedStore } = useSelectedStore()
  const storeId = selectedStore?.id

  const productQuery = useProduct(storeId, validId)
  const categoriesQuery = useCategories(storeId)
  const cart = useCart()

  const product = productQuery.data
  const category = (categoriesQuery.data ?? []).find((item) => item.id === product?.category_id)
  const inStock = (product?.stock.quantity ?? 0) > 0

  let status: ProductPageStatus = 'ready'
  if (productQuery.isPending) {
    status = 'loading'
  } else if (productQuery.isError || !product) {
    status = 'not-found'
  }

  function addToCart() {
    if (!product || !selectedStore || !inStock) {
      return
    }
    cart.addLine(
      {
        productId: product.id,
        storeId: selectedStore.id,
        name: product.name,
        slug: product.slug,
        sku: product.sku,
        priceCents: product.price,
        imageUrl: productImagePublicUrl(product.image_url),
      },
      1,
    )
    toast.success('Added to cart')
  }

  return {
    status,
    selectedStore,
    product,
    category,
    inStock,
    addToCart,
  }
}
