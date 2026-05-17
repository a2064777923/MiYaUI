param(
  [string]$BaseUrl = "http://localhost:3001"
)

Write-Host "Checking Nuxt root..."
$root = Invoke-WebRequest -Uri "$BaseUrl/" -UseBasicParsing
if ($root.StatusCode -lt 200 -or $root.StatusCode -ge 400) {
  throw "Nuxt root is unavailable"
}

Write-Host "Checking sitemap..."
$sitemap = Invoke-WebRequest -Uri "$BaseUrl/sitemap.xml" -UseBasicParsing
if ($sitemap.Content -notmatch "<urlset") {
  throw "Sitemap output is invalid"
}

Write-Host "Checking robots..."
$robots = Invoke-WebRequest -Uri "$BaseUrl/robots.txt" -UseBasicParsing
if ($robots.Content -notmatch "Sitemap:") {
  throw "Robots output is invalid"
}

Write-Host "Phase 2 smoke checks passed."
