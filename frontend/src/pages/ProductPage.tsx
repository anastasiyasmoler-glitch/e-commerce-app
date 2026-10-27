import { Link } from 'react-router-dom'
import { ProductImage } from '@/components/catalog/ProductImage'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import { Button } from '@/components/ui/button'
import { Container } from '@/components/ui/container'
import { PageLoading } from '@/components/ui/loading-screen'
import { useProductPage } from '@/hooks/useProductPage'
import { categoryPath, paths } from '@/paths'

export function ProductPage() {
  const productPage = useProductPage()

  if (productPage.status === 'loading') {
    return <PageLoading />
  }

  const { product, category, selectedStore, inStock } = productPage

  return (
    <Container className="pb-4">
      <Breadcrumb className="mt-6">
        <BreadcrumbList>
          <BreadcrumbItem>
            <BreadcrumbLink asChild>
              <Link to={paths.home}>Home</Link>
            </BreadcrumbLink>
          </BreadcrumbItem>
          <BreadcrumbSeparator />
          <BreadcrumbItem>
            <BreadcrumbLink asChild>
              <Link to={paths.catalog}>Catalog</Link>
            </BreadcrumbLink>
          </BreadcrumbItem>
          {category ? (
            <>
              <BreadcrumbSeparator />
              <BreadcrumbItem>
                <BreadcrumbLink asChild>
                  <Link to={categoryPath(category.slug)}>{category.name}</Link>
                </BreadcrumbLink>
              </BreadcrumbItem>
            </>
          ) : null}
          {product ? (
            <>
              <BreadcrumbSeparator />
              <BreadcrumbItem>
                <BreadcrumbPage>{product.name}</BreadcrumbPage>
              </BreadcrumbItem>
            </>
          ) : null}
        </BreadcrumbList>
      </Breadcrumb>

      {productPage.status === 'not-found' || !product ? (
        <div className="mx-auto max-w-[480px] px-4 py-20 text-center">
          <h2 className="mb-2 font-display text-[28px] font-medium">Product not found</h2>
          <p className="text-muted">This item is not in the selected store.</p>
        </div>
      ) : (
        <div className="mt-6 grid grid-cols-1 items-start gap-8 lg:grid-cols-[1.15fr_1fr] lg:gap-16">
          <div>
            <div className="relative aspect-square overflow-hidden rounded-[12px] bg-subtle [&_img]:size-full [&_img]:object-cover [&_svg]:block [&_svg]:size-full">
              <ProductImage name={product.name} slug={product.slug} imageUrl={product.image_url} />
            </div>
          </div>

          <div>
            <div className="text-[12px] font-semibold tracking-[0.1em] text-brand uppercase">
              {selectedStore?.name}
            </div>
            <h1 className="mt-2 mb-4 font-display text-[28px] leading-[1.15] font-medium tracking-[-0.02em] sm:text-[36px]">
              {product.name}
            </h1>
            <div className="flex flex-wrap items-baseline gap-4 border-b border-line pb-6">
              {product.price > 0 ? (
                <span className="text-[22px] font-semibold text-ink">
                  $
                  {(product.price / 100).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                  })}
                </span>
              ) : null}
              <span
                className={`inline-flex items-center gap-1.5 text-[13px] ${
                  inStock ? 'text-success' : 'text-danger'
                }`}
              >
                <span className="size-[7px] rounded-full bg-current" aria-hidden />
                {inStock ? 'In stock' : 'Out of stock'}
              </span>
            </div>
            {product.description ? <p className="py-6 text-muted">{product.description}</p> : null}
            <div className="mb-8 grid grid-cols-1 gap-3">
              <Button
                type="button"
                disabled
                className="h-[52px] w-full rounded-[6px] bg-subtle px-8 text-[16px] font-semibold text-muted"
              >
                Add to cart
              </Button>
            </div>
            <table className="w-full border-collapse">
              <caption className="pb-3 text-left text-[13px] font-semibold tracking-[0.08em] uppercase">
                Details
              </caption>
              <tbody>
                <tr className="border-t border-line text-[15px]">
                  <td className="w-[42%] py-3 text-muted">SKU</td>
                  <td className="py-3">{product.sku}</td>
                </tr>
                <tr className="border-t border-line text-[15px]">
                  <td className="w-[42%] py-3 text-muted">Available</td>
                  <td className="py-3">{product.stock.quantity}</td>
                </tr>
                <tr className="border-t border-line text-[15px]">
                  <td className="w-[42%] py-3 text-muted">Reserved</td>
                  <td className="py-3">{product.stock.reserved}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      )}
    </Container>
  )
}
