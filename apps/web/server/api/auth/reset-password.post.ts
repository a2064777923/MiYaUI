export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const body = await readBody(event)
  return await $fetch(`${config.fastapiUrl}/api/v1/auth/reset-password`, {
    method: 'POST',
    body,
  })
})
