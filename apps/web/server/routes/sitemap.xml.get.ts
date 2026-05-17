export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const items = await $fetch<{ path: string; updated_at?: string | null }[]>(`${config.fastapiUrl}/api/v1/content/sitemap`)
  const baseUrl = config.public.siteUrl || 'http://localhost:3001'
  const xml = [
    '<?xml version="1.0" encoding="UTF-8"?>',
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
    ...items.map((item) => {
      const loc = `${baseUrl.replace(/\/$/, '')}${item.path}`
      const lastmod = item.updated_at ? `<lastmod>${item.updated_at}</lastmod>` : ''
      return `<url><loc>${loc}</loc>${lastmod}</url>`
    }),
    '</urlset>',
  ].join('')
  setHeader(event, 'content-type', 'application/xml; charset=utf-8')
  return xml
})
