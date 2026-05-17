import { test, expect } from '@playwright/test'

test('unknown page falls back without blank page', async ({ page }) => {
  const response = await page.goto('/definitely-not-migrated')
  expect(response?.status()).toBe(200)
  await expect(page.locator('body')).not.toHaveText('')
  await expect(page.locator('body')).not.toContainText('Proxy to')
  await expect(page.locator('body')).not.toContainText('502')
  await expect(page.locator('iframe[title="Legacy WordPress page"]')).toBeVisible()
})

test('legacy WordPress fallback proxy rewrites local assets', async ({ page }) => {
  const consoleErrors: string[] = []
  const failedRequests: string[] = []

  page.on('console', (message) => {
    if (message.type() === 'error') {
      consoleErrors.push(message.text())
    }
  })
  page.on('pageerror', (error) => {
    consoleErrors.push(error.message)
  })
  page.on('requestfailed', (request) => {
    failedRequests.push(request.url())
  })

  const response = await page.goto('/__wp/definitely-not-migrated')
  expect(response?.status()).toBe(200)
  await expect(page.locator('body')).toContainText(/未找到页面|蜜芽设计/)

  expect(consoleErrors).toEqual([])
  expect(failedRequests).toEqual([])
})
