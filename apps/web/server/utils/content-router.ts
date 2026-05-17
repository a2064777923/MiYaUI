const MIGRATED_TYPES = new Set<string>([])

export function isMigratedContentType(path: string): boolean {
  const contentType = path.split('/')[0]
  return MIGRATED_TYPES.has(contentType)
}

export function getBackendUrl(path: string, config: { fastapiUrl: string; wordpressUrl: string }): string {
  if (isMigratedContentType(path)) {
    return `${config.fastapiUrl}/api/v1/content/${path}`
  }
  return `${config.wordpressUrl}/wp-json/b2/v1/${path}`
}
