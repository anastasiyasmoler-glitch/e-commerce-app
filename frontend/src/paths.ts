export const paths = {
  home: '/',
  profile: '/profile',
  catalog: '/categories',
  category: '/categories/:slug',
  product: '/products/:id',
} as const

export function categoryPath(slug: string): string {
  return `/categories/${slug}`
}

export function productPath(id: number): string {
  return `/products/${id}`
}
