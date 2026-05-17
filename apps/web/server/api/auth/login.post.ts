export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()
  const body = await readBody(event)
  const response = await fetch(`${config.fastapiUrl}/api/v1/auth/login`, {
    method: 'POST',
    headers: {
      'content-type': 'application/json',
      cookie: getHeader(event, 'cookie') || '',
    },
    body: JSON.stringify(body),
  })

  const setCookie = response.headers.get('set-cookie')
  if (setCookie) {
    appendHeader(event, 'set-cookie', setCookie)
  }

  const payload = await response.json()
  if (!response.ok) {
    throw createError({
      statusCode: response.status,
      statusMessage: payload.detail || 'Login failed',
    })
  }
  return payload
})
