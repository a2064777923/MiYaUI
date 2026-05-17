const DEAD_MEDIA_HOSTS = new Set(['images.uiiiuiii.com'])

function escapeHtml(value: string) {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}

function getAttribute(tag: string, name: string) {
  const match = tag.match(new RegExp(`${name}\\s*=\\s*(['"])(.*?)\\1`, 'i'))
  return match?.[2] || ''
}

function isDeadMediaUrl(rawUrl: string) {
  try {
    const url = new URL(rawUrl)
    return DEAD_MEDIA_HOSTS.has(url.hostname)
  } catch {
    return false
  }
}

function missingMediaPlaceholder(rawTag: string, rawUrl: string) {
  const alt = getAttribute(rawTag, 'alt')
  const width = getAttribute(rawTag, 'width')
  const height = getAttribute(rawTag, 'height')
  const size = [width, height].filter(Boolean).join(' x ')

  return [
    `<figure class="miya-missing-media" data-original-src="${escapeHtml(rawUrl)}">`,
    '<div class="miya-missing-media__box">',
    '<span>Media unavailable in local dev</span>',
    size ? `<small>${escapeHtml(size)}</small>` : '',
    '</div>',
    alt ? `<figcaption>${escapeHtml(alt)}</figcaption>` : '',
    `<a href="${escapeHtml(rawUrl)}" target="_blank" rel="noreferrer noopener">Original media URL</a>`,
    '</figure>',
  ].join('')
}

export function renderContentHtml(html: string) {
  return html.replace(/<img\b[^>]*\bsrc\s*=\s*(['"])(.*?)\1[^>]*>/gi, (tag, _quote, src) => {
    if (!isDeadMediaUrl(src)) {
      return tag
    }

    return missingMediaPlaceholder(tag, src)
  })
}
