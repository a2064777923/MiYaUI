<script setup lang="ts">
import ContentList from '~/components/content/ContentList.vue'
import SeoJsonLd from '~/components/content/SeoJsonLd.vue'
import { buildContentSeo } from '~/utils/seo'

const { data } = await useFetch('/api/content/resolve', {
  query: {
    path: '/',
  },
})
const config = useRuntimeConfig()

const seo = computed(() => buildContentSeo({
  title: data.value?.title || 'MiyaUI',
  description: data.value?.description || 'Latest migrated content',
  path: '/',
  siteUrl: config.public.siteUrl,
}))

useSeoMeta({
  title: () => seo.value.title,
  description: () => seo.value.description,
  ogTitle: () => seo.value.openGraph.title,
  ogDescription: () => seo.value.openGraph.description,
  ogUrl: () => seo.value.openGraph.url,
})

useHead({
  link: [
    {
      rel: 'canonical',
      href: () => seo.value.canonical,
    },
  ],
})
</script>

<template>
  <div class="miya-shell" style="padding: 32px 0 64px;">
    <SeoJsonLd :title="seo.title" :description="seo.description" :url="seo.canonical" />
    <ContentList
      :title="data?.title || 'MiyaUI'"
      :description="data?.description || 'Latest migrated content'"
      :items="data?.items || []"
    />
  </div>
</template>
