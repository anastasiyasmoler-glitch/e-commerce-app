export type ArtKind = 'sofa' | 'bed' | 'table' | 'chair' | 'storage' | 'lamp' | 'linen' | 'vase' | 'hero'

const categoryArtBySlug: Record<string, ArtKind> = {
  sofas: 'sofa',
  'straight-sofas': 'sofa',
  'corner-sofas': 'sofa',
  beds: 'bed',
  tables: 'table',
  chairs: 'chair',
  chandeliers: 'lamp',
  'floor-lamps': 'lamp',
  'table-lamps': 'lamp',
  'wall-lights': 'lamp',
  bedding: 'linen',
  'blankets-throws': 'linen',
  curtains: 'linen',
  towels: 'linen',
  wardrobes: 'storage',
  shelving: 'storage',
  decor: 'vase',
}

export function artForCategory(slug: string): ArtKind {
  return categoryArtBySlug[slug] ?? 'vase'
}

export function artForProductSlug(slug: string): ArtKind {
  if (slug.includes('sofa')) return 'sofa'
  if (slug.includes('bed')) return 'bed'
  if (slug.includes('table')) return 'table'
  if (slug.includes('chair') || slug.includes('stool')) return 'chair'
  if (slug.includes('lamp') || slug.includes('light') || slug.includes('chandelier')) return 'lamp'
  if (
    slug.includes('linen') ||
    slug.includes('bedding') ||
    slug.includes('blanket') ||
    slug.includes('curtain') ||
    slug.includes('towel')
  ) {
    return 'linen'
  }
  if (slug.includes('wardrobe') || slug.includes('shelf') || slug.includes('storage') || slug.includes('dresser')) {
    return 'storage'
  }
  return 'vase'
}
