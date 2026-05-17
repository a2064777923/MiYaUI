const MIGRATED_PATTERNS = [
  /^\/$/,
  /^\/\d+\.html$/,
  /^\/category\/[^/]+\/?$/,
  /^\/tag\/[^/]+\/?$/,
  /^\/author\/[^/]+\/?$/,
  /^\/search\/?$/,
]

export function isMigratedPath(path: string): boolean {
  return MIGRATED_PATTERNS.some((pattern) => pattern.test(path))
}

export function normalizeRoutePath(path: string | string[]) {
  const joined = Array.isArray(path) ? path.join('/') : path
  const normalized = `/${joined || ''}`.replace(/\/{2,}/g, '/')
  return normalized === '' ? '/' : normalized
}

export function getFastApiResolveUrl(
  path: string,
  config: { fastapiUrl: string },
  query: Record<string, string | string[] | undefined> = {},
) {
  const params = new URLSearchParams()
  params.set('path', path)
  for (const [key, value] of Object.entries(query)) {
    if (value === undefined) {
      continue
    }
    if (Array.isArray(value)) {
      for (const item of value) {
        params.append(key, item)
      }
      continue
    }
    params.set(key, value)
  }
  return `${config.fastapiUrl}/api/v1/content/resolve?${params.toString()}`
}

export function getWordPressPageUrl(
  path: string,
  config: { wordpressUrl: string },
  query: Record<string, string | string[] | undefined> = {},
) {
  const url = new URL(path, `${config.wordpressUrl.replace(/\/$/, '')}/`)
  for (const [key, value] of Object.entries(query)) {
    if (value === undefined) {
      continue
    }
    if (Array.isArray(value)) {
      value.forEach((item) => url.searchParams.append(key, item))
      continue
    }
    url.searchParams.set(key, value)
  }
  return url.toString()
}
