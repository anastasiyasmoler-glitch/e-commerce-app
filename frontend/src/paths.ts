export const paths = {
  home: '/',
  profile: '/profile',
  assistant: '/assistant',
  catalog: '/categories',
  category: '/categories/:slug',
  product: '/products/:id',
  search: '/search',
  cart: '/cart',
  checkout: '/checkout',
  orders: '/orders',
  order: '/orders/:id',
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

export function orderPath(id: number): string {
  return `/orders/${id}`
}
