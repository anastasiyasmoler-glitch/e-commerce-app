import { artForProductSlug } from '@/catalog/art'
import { FurnitureArt } from '@/components/catalog/FurnitureArt'

type ProductImageProps = {
  name: string
  slug: string
  imageUrl?: string | null
  className?: string
}

export function ProductImage({ name, slug, imageUrl, className }: ProductImageProps) {
  if (imageUrl) {
    return (
      <img
        src={imageUrl}
        alt={name}
        className={className ?? 'size-full object-cover'}
        loading="lazy"
      />
    )
  }

  return <FurnitureArt kind={artForProductSlug(slug)} label={name} />
}
