<script setup lang="ts">
defineProps<{
  title?: string
  description?: string
  items: Array<{
    id: number
    title: string
    excerpt?: string
    original_url_path: string
    content_type: string
    seo_description?: string | null
  }>
}>()
</script>

<template>
  <section class="miya-grid">
    <header class="miya-card" style="padding: 28px;">
      <p class="miya-muted" style="margin: 0 0 8px;">Migrated Content</p>
      <h1 style="margin: 0 0 12px; font-size: clamp(2rem, 4vw, 3.4rem);">
        {{ title || 'MiyaUI' }}
      </h1>
      <p class="miya-muted" style="margin: 0;">
        {{ description || 'Latest synchronized content from the legacy site.' }}
      </p>
    </header>

    <div class="miya-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
      <NuxtLink
        v-for="item in items"
        :key="item.id"
        :to="item.original_url_path"
        class="miya-card"
        style="padding: 24px; display: grid; gap: 12px;"
      >
        <div class="miya-muted" style="text-transform: uppercase; letter-spacing: 0.08em; font-size: 12px;">
          {{ item.content_type }}
        </div>
        <h2 style="margin: 0; font-size: 1.35rem;">{{ item.title }}</h2>
        <p class="miya-muted" style="margin: 0;">
          {{ item.seo_description || item.excerpt }}
        </p>
      </NuxtLink>
    </div>
  </section>
</template>
