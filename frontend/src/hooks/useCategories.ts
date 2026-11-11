import { useQuery } from '@tanstack/react-query'
import { catalogKeys, fetchCategories } from '@/api/catalogApi'

export function useCategories(storeId: number | undefined, parentId?: number | null) {
  return useQuery({
    queryKey: catalogKeys.categories(storeId, parentId),
    queryFn: () => fetchCategories(storeId!, parentId),
    enabled: storeId !== undefined,
  })
}

/** id → name for search category facets (roots + one level of children). */
export function useStoreCategoryNames(storeId: number | undefined) {
  return useQuery({
    queryKey: ['catalog', 'category-names', storeId],
    enabled: storeId !== undefined,
    queryFn: async () => {
      const roots = await fetchCategories(storeId!)
      const names: Record<string, string> = {}
      for (const category of roots) {
        names[String(category.id)] = category.name
      }
      await Promise.all(
        roots.map(async (root) => {
          const children = await fetchCategories(storeId!, root.id)
          for (const category of children) {
            names[String(category.id)] = category.name
          }
        }),
      )
      return names
    },
  })
}
