import { http } from './http'

export type CatalogStore = {
  id: number
  name: string
  slug: string
}

export type CatalogCategory = {
  id: number
  store_id: number
  parent_id: number | null
  name: string
  slug: string
}

export type CatalogStock = {
  quantity: number
  reserved: number
}

export type CatalogProduct = {
  id: number
  store_id: number
  category_id: number
  name: string
  slug: string
  description: string | null
  sku: string
  price: number
  attributes?: Record<string, string>
  image?: string | null
  image_url?: string | null
  stock: CatalogStock
}

export const catalogKeys = {
  all: ['catalog'] as const,
  stores: ['catalog', 'stores'] as const,
  categories: (storeId: number | undefined, parentId?: number | null) =>
    ['catalog', 'categories', storeId, parentId ?? 'roots'] as const,
  products: (storeId: number | undefined, params?: Record<string, unknown>) =>
    ['catalog', 'products', storeId, params ?? {}] as const,
  product: (storeId: number | undefined, id: number | undefined) =>
    ['catalog', 'product', storeId, id] as const,
}

export async function fetchStores(): Promise<CatalogStore[]> {
  const { data } = await http.get<{ data: CatalogStore[] }>('/api/catalog/stores')
  return data.data
}

export async function fetchCategories(storeId: number, parentId?: number | null): Promise<CatalogCategory[]> {
  const { data } = await http.get<{ data: CatalogCategory[] }>('/api/catalog/categories', {
    params: { store_id: storeId, parent_id: parentId ?? undefined },
  })
  return data.data
}

export async function fetchProducts(
  storeId: number,
  params?: { category_id?: number; page?: number; per_page?: number },
): Promise<CatalogProduct[]> {
  const { data } = await http.get<{ data: CatalogProduct[] }>('/api/catalog/products', {
    params: { store_id: storeId, ...params },
  })
  return data.data
}

export async function fetchProduct(storeId: number, id: number): Promise<CatalogProduct> {
  const { data } = await http.get<{ data: CatalogProduct }>(`/api/catalog/products/${id}`, {
    params: { store_id: storeId },
  })
  return data.data
}
