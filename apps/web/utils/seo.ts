type SeoInput = {
  title?: string | null
  description?: string | null
  path?: string | null
  image?: string | null
  siteUrl?: string | null
}

export function buildCanonicalUrl(path: string | null | undefined, siteUrl?: string | null) {
  const baseUrl = siteUrl || 'http://localhost:3001'
  return `${baseUrl.replace(/\/$/, '')}${path || '/'}`
}

export function buildContentSeo(input: SeoInput) {
  const canonical = buildCanonicalUrl(input.path, input.siteUrl)
  const title = input.title || 'MiyaUI'
  const description = input.description || 'MiyaUI migrated content experience'
  const image = input.image || `${canonical.replace(/\/$/, '')}/og-default.png`

  return {
    title,
    description,
    canonical,
    openGraph: {
      title,
      description,
      url: canonical,
      images: [{ url: image }],
      type: 'article',
    },
    twitter: {
      card: 'summary_large_image',
      title,
      description,
      images: [image],
    },
  }
}
