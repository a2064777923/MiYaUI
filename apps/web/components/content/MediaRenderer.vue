<script setup lang="ts">
defineProps<{
  media?: Array<{
    id: number
    source_url: string
    title?: string
    alt_text?: string | null
  }>
}>()
</script>

<template>
  <div v-if="media?.length" class="miya-grid">
    <figure
      v-for="asset in media"
      :key="asset.id"
      class="miya-card"
      style="padding: 16px;"
    >
      <img :src="asset.source_url" :alt="asset.alt_text || asset.title || ''">
      <figcaption v-if="asset.title" class="miya-muted" style="margin-top: 12px;">
        {{ asset.title }}
      </figcaption>
    </figure>
  </div>
</template>
