import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { catalogKeys, fetchProducts, type CatalogProduct } from '@/api/catalogApi'
import { useCategories } from './useCategories'

const PAGE_SIZE = 9

export function useProducts(
  storeId: number | undefined,
  params?: { category_id?: number; page?: number; per_page?: number; section?: string },
) {
  const { section: _section, ...request } = params ?? {}

  return useQuery({
    queryKey: catalogKeys.products(storeId, params),
    queryFn: () => fetchProducts(storeId!, request),
    enabled: storeId !== undefined,
  })
}

export function useCategoryListing(storeId: number | undefined, slug: string | undefined, page: number) {
  const roots = useCategories(storeId)
  const current = slug ? (roots.data ?? []).find((category) => category.slug === slug) ?? null : null
  const children = useCategories(storeId, current?.id ?? null)

  const products = useQuery({
    queryKey: catalogKeys.products(storeId, { listing: slug ?? 'all', page }),
    queryFn: async () => {
      if (!current) {
        return fetchProducts(storeId!, { page, per_page: PAGE_SIZE })
      }

      const ids = [current.id, ...(children.data ?? []).map((category) => category.id)]
      const batches = await Promise.all(
        ids.map((categoryId) => fetchProducts(storeId!, { category_id: categoryId, per_page: 50 })),
      )
      const seen = new Set<number>()
      const merged: CatalogProduct[] = []
      for (const batch of batches) {
        for (const product of batch) {
          if (!seen.has(product.id)) {
            seen.add(product.id)
            merged.push(product)
          }
        }
      }
      const start = (page - 1) * PAGE_SIZE
      return merged.slice(start, start + PAGE_SIZE)
    },
    placeholderData: keepPreviousData,
    enabled:
      storeId !== undefined &&
      roots.isSuccess &&
      (!slug || current === null || children.isFetched),
  })

  return { roots, current, products, pageSize: PAGE_SIZE }
}
