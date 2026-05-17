export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const response = await fetch(`${config.fastapiUrl}/api/v1/auth/logout`, {
    method: 'POST',
    headers: {
      cookie: getHeader(event, 'cookie') || '',
    },
  })

  const setCookie = response.headers.get('set-cookie')
  if (setCookie) {
    appendHeader(event, 'set-cookie', setCookie)
  }

  return await response.json()
})
