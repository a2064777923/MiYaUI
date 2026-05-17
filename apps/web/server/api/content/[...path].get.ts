import { getFastApiResolveUrl, normalizeRoutePath } from '~/server/utils/content-router'

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const rawPath = getRouterParam(event, 'path') || ''
  const path = normalizeRoutePath(rawPath)
  const query = getQuery(event)

  try {
    return await $fetch(getFastApiResolveUrl(path, config, query))
  } catch (error) {
    throw createError({
      statusCode: 502,
      statusMessage: 'Failed to resolve migrated content',
    })
  }
})
