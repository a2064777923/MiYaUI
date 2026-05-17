import { getFastApiResolveUrl } from '~/server/utils/content-router'

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const query = getQuery(event)
  const path = typeof query.path === 'string' ? query.path : '/'

  try {
    return await $fetch(getFastApiResolveUrl(path, config, query))
  } catch (error) {
    throw createError({
      statusCode: 502,
      statusMessage: 'Failed to resolve root content',
    })
  }
})
