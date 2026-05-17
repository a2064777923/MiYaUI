import { test, expect } from '@playwright/test'

test('login page exposes first-release social providers', async ({ page }) => {
  await page.goto('/login')

  await expect(page.getByRole('heading', { name: '登录 MiyaUI' })).toBeVisible()

  for (const provider of ['WeChat', 'QQ', 'Weibo', 'Baidu', 'Google']) {
    await expect(page.getByRole('button', { name: provider })).toBeVisible()
  }
})
