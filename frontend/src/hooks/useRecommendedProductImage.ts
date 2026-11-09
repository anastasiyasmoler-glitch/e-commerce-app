import { useQuery } from '@tanstack/react-query'
import type { RecommendedProduct } from '@/api/recommendationApi'
import { catalogKeys, fetchProduct } from '@/api/catalogApi'
import { productImagePublicUrl } from '@/lib/catalog/productImageUrl'

export function useRecommendedProductImage(product: RecommendedProduct): string | undefined {
  const fromRecommendation = productImagePublicUrl(product.image_url)

  const catalogQuery = useQuery({
    queryKey: catalogKeys.product(product.store_id, product.product_id),
    queryFn: () => fetchProduct(product.store_id, product.product_id),
    enabled: fromRecommendation === undefined,
    staleTime: 5 * 60 * 1000,
  })

  if (fromRecommendation !== undefined) {
    return fromRecommendation
  }

  const catalog = catalogQuery.data
  if (!catalog) {
    return undefined
  }

  return productImagePublicUrl(catalog.image_url ?? catalog.image ?? null)
}
