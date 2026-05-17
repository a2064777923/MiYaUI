<script setup lang="ts">
const props = defineProps<{
  title: string
  description: string
  url: string
}>()

useHead({
  script: [
    {
      type: 'application/ld+json',
      children: JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: props.title,
        description: props.description,
        url: props.url,
      }),
    },
  ],
})
</script>

<template>
  <span style="display: none;" />
</template>
