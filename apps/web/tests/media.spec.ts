import { test, expect } from '@playwright/test'

test('page renders without broken root shell', async ({ page }) => {
  await page.goto('/')
  await expect(page.locator('body')).toBeVisible()
})
