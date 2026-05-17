import { defineConfig } from '@playwright/test'

const chromiumExecutablePath = process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH

export default defineConfig({
  testDir: './tests',
  use: {
    baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:3001',
    trace: 'retain-on-failure',
    launchOptions: chromiumExecutablePath
      ? {
          executablePath: chromiumExecutablePath,
          args: ['--no-sandbox', '--disable-dev-shm-usage'],
        }
      : undefined,
  },
  webServer: undefined,
})
