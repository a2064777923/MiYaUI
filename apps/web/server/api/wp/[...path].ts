import { getBackendUrl } from '~/server/utils/content-router'

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const path = getRouterParam(event, 'path') || ''
  const query = getQuery(event)

  const backendUrl = getBackendUrl(path, config)

  try {
    return await $fetch(backendUrl, {
      query,
      headers: {
        Authorization: getHeader(event, 'authorization') || '',
      },
    })
  } catch (error) {
    throw createError({
      statusCode: 502,
      statusMessage: `Proxy to ${backendUrl} failed`,
    })
  }
})
