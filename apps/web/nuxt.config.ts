export default defineNuxtConfig({
  devtools: { enabled: true },
  modules: ['@nuxt/ui', '@pinia/nuxt'],
  runtimeConfig: {
    fastapiUrl: process.env.FASTAPI_URL || 'http://localhost:8000',
    wordpressUrl: process.env.WORDPRESS_URL || 'http://localhost:8082',
    public: {
      appName: 'MiyaUI',
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
