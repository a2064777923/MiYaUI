import { test, expect } from '@playwright/test'

test('home page exposes canonical metadata', async ({ page }) => {
  await page.goto('/')
  await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', /http/)
})
