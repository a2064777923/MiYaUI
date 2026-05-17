export default defineEventHandler((event) => {
  const config = useRuntimeConfig()
  const baseUrl = config.public.siteUrl || 'http://localhost:3001'
  setHeader(event, 'content-type', 'text/plain; charset=utf-8')
  return `User-agent: *\nAllow: /\nSitemap: ${baseUrl.replace(/\/$/, '')}/sitemap.xml\n`
})
