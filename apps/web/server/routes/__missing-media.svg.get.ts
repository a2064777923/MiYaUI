const svg = [
  '<svg xmlns="http://www.w3.org/2000/svg" width="960" height="540" viewBox="0 0 960 540">',
  '<rect width="960" height="540" fill="#f3f4f6"/>',
  '<rect x="80" y="80" width="800" height="380" rx="28" fill="#ffffff" stroke="#d1d5db" stroke-width="2"/>',
  '<text x="480" y="260" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" fill="#374151">Media unavailable</text>',
  '<text x="480" y="310" text-anchor="middle" font-family="Arial, sans-serif" font-size="22" fill="#6b7280">Original external asset could not be resolved</text>',
  '</svg>',
].join('')

export default defineEventHandler(() => {
  return new Response(svg, {
    headers: {
      'content-type': 'image/svg+xml; charset=utf-8',
      'cache-control': 'public, max-age=3600',
    },
  })
})
