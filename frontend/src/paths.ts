export const paths = {
  home: '/',
  profile: '/profile',
  catalog: '/categories',
  category: '/categories/:slug',
  product: '/products/:id',
  search: '/search',
} as const

export function searchPath(query?: string): string {
  if (!query?.trim()) {
    return paths.search
  }
  return `${paths.search}?q=${encodeURIComponent(query.trim())}`
}

export function categoryPath(slug: string): string {
  return `/categories/${slug}`
}

export function productPath(id: number): string {
  return `/products/${id}`
}
