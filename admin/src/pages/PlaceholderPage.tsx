type PlaceholderPageProps = {
  title: string
  hint: string
}

export function PlaceholderPage({ title, hint }: PlaceholderPageProps) {
  return (
    <>
      <div className="admin-header">
        <h1 className="admin-title">{title}</h1>
      </div>
      <p className="placeholder">{hint}</p>
    </>
  )
}
