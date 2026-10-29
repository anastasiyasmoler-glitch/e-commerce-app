import { fetchCategories, type CatalogCategory } from '@/api/catalogApi'

export async function findCategoryBySlug(
  storeId: number,
  slug: string,
): Promise<CatalogCategory | null> {
  const roots = await fetchCategories(storeId)
  const atRoot = roots.find((category) => category.slug === slug)
  if (atRoot) {
    return atRoot
  }

  for (const root of roots) {
    const children = await fetchCategories(storeId, root.id)
    const match = children.find((category) => category.slug === slug)
    if (match) {
      return match
    }
  }

  return null
}
