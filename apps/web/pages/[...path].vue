<script setup lang="ts">
import ContentDetail from '~/components/content/ContentDetail.vue'
import ContentList from '~/components/content/ContentList.vue'
import SeoJsonLd from '~/components/content/SeoJsonLd.vue'
import { buildContentSeo } from '~/utils/seo'

const route = useRoute()
const path = computed(() => route.path || '/')
const config = useRuntimeConfig()

const { data } = await useFetch(() => `/api/content${path.value}`)

const seo = computed(() => {
  const payload = data.value as any
  return buildContentSeo({
    title: payload?.title || payload?.item?.seo_title || payload?.item?.title,
    description: payload?.description || payload?.item?.seo_description || payload?.item?.excerpt,
    path: path.value,
    siteUrl: config.public.siteUrl,
  })
})

useSeoMeta({
  title: () => seo.value.title,
  description: () => seo.value.description,
  ogTitle: () => seo.value.openGraph.title,
  ogDescription: () => seo.value.openGraph.description,
  ogUrl: () => seo.value.openGraph.url,
  twitterTitle: () => seo.value.twitter.title,
  twitterDescription: () => seo.value.twitter.description,
})

useHead({
  link: [
    {
      rel: 'canonical',
      href: () => seo.value.canonical,
    },
  ],
})

const legacyFrameUrl = computed(() => `/__wp${route.fullPath}`)
</script>

<template>
  <div class="miya-shell" style="padding: 32px 0 64px;">
    <SeoJsonLd
      v-if="data?.status === 'ok'"
      :title="seo.title"
      :description="seo.description"
      :url="seo.canonical"
    />

    <ContentDetail v-if="data?.view === 'detail' && data.item" :item="data.item" />

    <ContentList
      v-else-if="['home', 'taxonomy', 'author', 'search'].includes(data?.view || '')"
      :title="data?.title"
      :description="data?.description"
      :items="data?.items || []"
    />

    <section
      v-else
      class="miya-card miya-legacy-fallback"
    >
      <div class="miya-legacy-fallback__bar">
        <div>
          <p class="miya-legacy-fallback__eyebrow">
            Legacy WordPress fallback
          </p>
          <h1>此頁面暫由 WordPress 測試環境渲染</h1>
        </div>
        <a :href="legacyFrameUrl" target="_blank" rel="noreferrer noopener">
          在新分頁打開
        </a>
      </div>
      <iframe
        class="miya-legacy-fallback__frame"
        :src="legacyFrameUrl"
        title="Legacy WordPress page"
      />
    </section>
  </div>
</template>

<style scoped>
.miya-legacy-fallback {
  overflow: hidden;
  padding: 0;
}

.miya-legacy-fallback__bar {
  align-items: center;
  background: #0f172a;
  color: #f8fafc;
  display: flex;
  gap: 16px;
  justify-content: space-between;
  padding: 18px 22px;
}

.miya-legacy-fallback__bar h1,
.miya-legacy-fallback__bar p {
  margin: 0;
}

.miya-legacy-fallback__bar h1 {
  font-size: 18px;
  line-height: 1.4;
}

.miya-legacy-fallback__bar a {
  border: 1px solid rgb(248 250 252 / 45%);
  border-radius: 999px;
  color: #f8fafc;
  flex: 0 0 auto;
  padding: 8px 14px;
  text-decoration: none;
}

.miya-legacy-fallback__eyebrow {
  color: #93c5fd;
  font-size: 12px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.miya-legacy-fallback__frame {
  border: 0;
  display: block;
  height: min(1200px, 82vh);
  width: 100%;
}

@media (max-width: 640px) {
  .miya-legacy-fallback__bar {
    align-items: flex-start;
    flex-direction: column;
  }

  .miya-legacy-fallback__frame {
    height: 78vh;
  }
}
</style>
