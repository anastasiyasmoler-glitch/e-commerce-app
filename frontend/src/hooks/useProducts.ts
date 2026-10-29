import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { catalogKeys, fetchProducts, type CatalogProductsParams } from '@/api/catalogApi'
import { findCategoryBySlug } from '@/lib/catalog/categoryBySlug'
import { useCategories } from './useCategories'

export function useProducts(
  storeId: number | undefined,
  params?: CatalogProductsParams & { section?: string },
) {
  const { section: _section, ...request } = params ?? {}

  return useQuery({
    queryKey: catalogKeys.products(storeId, params),
    queryFn: () => fetchProducts(storeId!, request),
    enabled: storeId !== undefined,
  })
}

export function useCategoryListing(
  storeId: number | undefined,
  slug: string | undefined,
  page: number,
  perPage: number,
) {
  const roots = useCategories(storeId)

  const categoryBySlug = useQuery({
    queryKey: ['catalog', 'category-by-slug', storeId, slug ?? ''],
    queryFn: () => findCategoryBySlug(storeId!, slug!),
    enabled: storeId !== undefined && Boolean(slug),
  })

  const current = slug ? (categoryBySlug.data ?? null) : null
  const children = useCategories(storeId, current?.id ?? null)

  const categoryId = current?.id

  const products = useQuery({
    queryKey: catalogKeys.products(storeId, {
      category_id: categoryId,
      page,
      per_page: perPage,
    }),
    queryFn: () =>
      fetchProducts(storeId!, {
        category_id: categoryId,
        page,
        per_page: perPage,
      }),
    placeholderData: keepPreviousData,
    enabled:
      storeId !== undefined &&
      roots.isSuccess &&
      (!slug || categoryBySlug.isSuccess),
  })

  return { roots, current, children, categoryBySlug, products, pageSize: perPage }
}
