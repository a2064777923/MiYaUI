<script setup lang="ts">
import ContentList from '~/components/content/ContentList.vue'

const route = useRoute()
const query = computed(() => String(route.query.q || ''))

const { data } = await useFetch('/api/content/search', {
  query: computed(() => ({ q: query.value || 'MiyaUI' })),
})
</script>

<template>
  <div class="miya-shell" style="padding: 32px 0 64px;">
    <ContentList
      title="搜索结果"
      :description="`关键字：${query || 'MiyaUI'}`"
      :items="data?.items || []"
    />
  </div>
</template>
