export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const provider = getRouterParam(event, 'provider')
  return await $fetch(`${config.fastapiUrl}/api/v1/auth/social/${provider}`)
})
