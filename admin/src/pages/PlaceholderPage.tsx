import { PageHeader } from '@/components/ui/page-header'

type PlaceholderPageProps = {
  title: string
  hint: string
}

export function PlaceholderPage({ title, hint }: PlaceholderPageProps) {
  return (
    <>
      <PageHeader title={title} />
      <p className="muted">{hint}</p>
    </>
  )
}
