export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()

  try {
    const result = await $fetch(`${config.fastapiUrl}/api/v1/health`)
    return { status: 'ok', backend: result }
  } catch (error) {
    throw createError({
      statusCode: 502,
      statusMessage: 'Backend unavailable',
    })
  }
})
