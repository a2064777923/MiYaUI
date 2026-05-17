export default defineNuxtConfig({
  devtools: { enabled: true },
  experimental: {
    appManifest: false,
    checkOutdatedBuildInterval: false,
  },
  modules: ['@nuxt/ui', '@pinia/nuxt'],
  css: ['~/assets/main.css'],
  runtimeConfig: {
    fastapiUrl: process.env.FASTAPI_URL || 'http://localhost:8000',
    wordpressUrl: process.env.WORDPRESS_URL || 'http://localhost:8082',
    public: {
      appName: 'MiyaUI',
      siteUrl: process.env.PUBLIC_SITE_URL || 'http://localhost:3001',
    },
  },
  nitro: {
    routeRules: {
      '/api/**': { cors: true },
    },
  },
  typescript: {
    strict: true,
  },
})
