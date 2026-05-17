import { getWordPressPageUrl, normalizeRoutePath } from '~/server/utils/content-router'

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const path = normalizeRoutePath(getRouterParam(event, 'path') || '')
  const query = getQuery(event)
  const backendUrl = getWordPressPageUrl(path, config, query)

  try {
    const response = await fetch(backendUrl, {
      headers: {
        authorization: getHeader(event, 'authorization') || '',
        cookie: getHeader(event, 'cookie') || '',
      },
    })
    const body = await response.text()
    return {
      status: response.status,
      url: backendUrl,
      html: body,
    }
  } catch (error) {
    throw createError({
      statusCode: 502,
      statusMessage: `Proxy to ${backendUrl} failed`,
    })
  }
})
