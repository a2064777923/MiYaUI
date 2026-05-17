const LEGACY_PROXY_PREFIX = '/__wp'
const DEAD_MEDIA_PLACEHOLDER = '/__missing-media.svg'
const DEAD_MEDIA_HOSTS = ['images.uiiiuiii.com', 'none']
const LOCAL_WORDPRESS_ORIGINS = [
  'http://localhost:8082',
  'http://127.0.0.1:8082',
  'http://host.docker.internal:8082',
  'http://wordpress',
]

function escapedOrigin(origin: string) {
  return origin.replaceAll('/', '\\/')
}

function uniqueOrigins(wordpressUrl: string) {
  return Array.from(
    new Set([
      wordpressUrl.replace(/\/$/, ''),
      ...LOCAL_WORDPRESS_ORIGINS,
    ].filter(Boolean)),
  )
}

function proxiedPath(path: string, proxyPrefix: string) {
  if (path.startsWith(`${LEGACY_PROXY_PREFIX}/`)) {
    return `${proxyPrefix}${path.slice(LEGACY_PROXY_PREFIX.length)}`
  }
  return `${proxyPrefix}${path}`
}

function rewriteRootRelativeAttributes(html: string, proxyPrefix: string) {
  return html.replace(
    /\b(src|href|action|poster)\s*=\s*(["'])(\/(?!\/|__wp(?:\/|$)|_nuxt(?:\/|$)|api(?:\/|$)|__missing-media\.svg)[^"']*)\2/gi,
    (_match, attribute, quote, path) => `${attribute}=${quote}${proxiedPath(path, proxyPrefix)}${quote}`,
  )
}

function rewriteCssUrls(html: string, proxyPrefix: string) {
  return html.replace(
    /url\((["']?)(\/(?!\/|__wp(?:\/|$)|_nuxt(?:\/|$)|api(?:\/|$)|__missing-media\.svg)[^)'" ]+)\1\)/gi,
    (_match, quote, path) => `url(${quote}${proxiedPath(path, proxyPrefix)}${quote})`,
  )
}

function rewriteDeadMediaHosts(html: string) {
  let rewritten = html
  for (const host of DEAD_MEDIA_HOSTS) {
    rewritten = rewritten.replace(
      new RegExp(`https?:\\/\\/${host.replaceAll('.', '\\.')}\\/[^"'\\s<>)]+`, 'gi'),
      DEAD_MEDIA_PLACEHOLDER,
    )
    rewritten = rewritten.replace(
      new RegExp(`https?:\\/\\/${host.replaceAll('.', '\\.')}(?=["'\\s<>)])`, 'gi'),
      DEAD_MEDIA_PLACEHOLDER,
    )
  }
  return rewritten
}

function injectLegacyFallbackGlobals(html: string) {
  const shim = [
    '<script>',
    'window.b2_write_data = window.b2_write_data || { cats: [], collections: [], cats_default: 0, collections_default: 0 };',
    '</script>',
  ].join('')

  return html.replace(/<head(\s[^>]*)?>/i, (head) => `${head}\n${shim}`)
}

function stripLocalDevOnlyScripts(html: string) {
  return html
    .replace(
      /<script\b[^>]*\bsrc=(["'])[^"']*\/wp-content\/themes\/b2\/Assets\/fontend\/write\.js[^"']*\1[^>]*>\s*<\/script>/gi,
      '',
    )
    .replace(
      /<script\b[^>]*\bsrc=(["'])https?:\/\/hm\.baidu\.com\/[^"']*\1[^>]*>\s*<\/script>/gi,
      '',
    )
}

export function rewriteWordPressHtmlForProxy(html: string, wordpressUrl: string, requestOrigin: string) {
  const proxyOrigin = requestOrigin.replace(/\/$/, '')
  const proxyPrefix = `${proxyOrigin}${LEGACY_PROXY_PREFIX}`
  let rewritten = html

  for (const origin of uniqueOrigins(wordpressUrl)) {
    if (origin === proxyPrefix) {
      continue
    }
    rewritten = rewritten.replaceAll(`${origin}/`, `${proxyPrefix}/`)
    rewritten = rewritten.replaceAll(origin, proxyPrefix)
    rewritten = rewritten.replaceAll(`${escapedOrigin(origin)}\\/`, `${proxyPrefix}/`)
    rewritten = rewritten.replaceAll(escapedOrigin(origin), proxyPrefix)
  }

  rewritten = rewriteRootRelativeAttributes(rewritten, proxyPrefix)
  rewritten = rewriteCssUrls(rewritten, proxyPrefix)
  rewritten = rewriteDeadMediaHosts(rewritten)
  rewritten = injectLegacyFallbackGlobals(rewritten)
  rewritten = stripLocalDevOnlyScripts(rewritten)

  return rewritten
}
