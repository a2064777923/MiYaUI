import { getWordPressPageUrl, normalizeRoutePath } from '~/server/utils/content-router'
import { rewriteWordPressHtmlForProxy } from '~/server/utils/wordpress-proxy'

const TEXTUAL_TYPES = [
  'text/html',
  'text/css',
  'text/javascript',
  'application/javascript',
  'application/json',
  'application/xml',
  'text/xml',
]

function responseHeaders(response: Response, contentType: string) {
  const headers = new Headers()
  headers.set('content-type', contentType)
  headers.set('x-robots-tag', 'noindex')

  for (const name of ['cache-control', 'etag', 'last-modified']) {
    const value = response.headers.get(name)
    if (value) {
      headers.set(name, value)
    }
  }

  return headers
}

function isTextual(contentType: string) {
  const normalized = contentType.toLowerCase()
  return TEXTUAL_TYPES.some((type) => normalized.includes(type))
}

function emptyJsonResponse() {
  return new Response('[]', {
    status: 200,
    headers: {
      'content-type': 'application/json; charset=utf-8',
      'cache-control': 'no-store',
      'x-robots-tag': 'noindex',
    },
  })
}

function patchLegacyScript(path: string, body: string) {
  if (path === '/wp-content/themes/b2Jitheme/Render/Js/ask.js') {
    return body.replace(
      'searchButton.addEventListener',
      'if (searchButton) searchButton.addEventListener',
    )
  }

  return body
}

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const path = normalizeRoutePath(getRouterParam(event, 'path') || '')
  const backendUrl = getWordPressPageUrl(path, config, getQuery(event))
  const requestOrigin = getRequestURL(event).origin

  if (path === '/wp-json/b2/v1/getLatestAnnouncement') {
    return emptyJsonResponse()
  }

  let response: Response
  try {
    response = await fetch(backendUrl, {
      headers: {
        accept: getHeader(event, 'accept') || '*/*',
        cookie: getHeader(event, 'cookie') || '',
        'user-agent': getHeader(event, 'user-agent') || 'MiyaUI local WordPress proxy',
      },
    })
  } catch {
    throw createError({
      statusCode: 502,
      statusMessage: `Proxy to ${backendUrl} failed`,
    })
  }

  const contentType = response.headers.get('content-type') || 'application/octet-stream'
  const headers = responseHeaders(response, contentType)

  if (contentType.toLowerCase().includes('text/html')) {
    const html = await response.text()
    return new Response(rewriteWordPressHtmlForProxy(html, config.wordpressUrl, requestOrigin), {
      status: response.status,
      headers,
    })
  }

  if (isTextual(contentType)) {
    return new Response(patchLegacyScript(path, await response.text()), {
      status: response.status,
      headers,
    })
  }

  return new Response(await response.arrayBuffer(), {
    status: response.status,
    headers,
  })
})
