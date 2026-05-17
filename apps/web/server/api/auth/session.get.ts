export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const response = await fetch(`${config.fastapiUrl}/api/v1/auth/session`, {
    headers: {
      cookie: getHeader(event, 'cookie') || '',
    },
  })

  if (response.status === 401) {
    return null
  }

  if (!response.ok) {
    throw createError({
      statusCode: response.status,
      statusMessage: 'Failed to read session',
    })
  }

  return await response.json()
})
