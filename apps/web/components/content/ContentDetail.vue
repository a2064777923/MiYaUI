<script setup lang="ts">
import MediaRenderer from '~/components/content/MediaRenderer.vue'
import { renderContentHtml } from '~/utils/contentHtml'

const props = defineProps<{
  item: {
    title: string
    body: string
    excerpt?: string
    author?: {
      display_name: string
      username: string
    } | null
    media?: Array<{
      id: number
      source_url: string
      title?: string
      alt_text?: string | null
    }>
  }
}>()

const renderedBody = computed(() => renderContentHtml(props.item.body))
</script>

<template>
  <article class="miya-grid">
    <header class="miya-card" style="padding: 32px;">
      <p class="miya-muted" style="margin: 0 0 8px;">
        {{ item.author ? `By ${item.author.display_name}` : 'Migrated article' }}
      </p>
      <h1 style="margin: 0; font-size: clamp(2.2rem, 4vw, 4rem); line-height: 1.05;">
        {{ item.title }}
      </h1>
      <p v-if="item.excerpt" class="miya-muted" style="margin: 16px 0 0; font-size: 1.05rem;">
        {{ item.excerpt }}
      </p>
    </header>

    <section class="miya-card" style="padding: 32px;">
      <div class="content-body" v-html="renderedBody" />
    </section>

    <MediaRenderer :media="item.media" />
  </article>
</template>
