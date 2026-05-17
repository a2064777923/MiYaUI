import { test, expect } from '@playwright/test'

test('home page renders migrated shell', async ({ page }) => {
  await page.goto('/')
  await expect(page.locator('body')).toContainText('MiyaUI')
})
