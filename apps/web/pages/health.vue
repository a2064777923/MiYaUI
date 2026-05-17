<script setup lang="ts">
const { data: health, status, error } = useFetch('/api/health')
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center gap-6 p-8">
    <h1 class="text-3xl font-bold">Health Check</h1>

    <div class="p-6 rounded-lg border max-w-md w-full space-y-4">
      <div class="flex items-center justify-between">
        <span>Frontend</span>
        <span class="text-green-500 font-medium">OK</span>
      </div>

      <div v-if="status === 'pending'" class="flex items-center justify-between">
        <span>Backend</span>
        <span class="text-gray-400">Checking...</span>
      </div>

      <div v-else-if="error" class="flex items-center justify-between">
        <span>Backend</span>
        <span class="text-red-500 font-medium">Unavailable</span>
      </div>

      <div v-else-if="health" class="space-y-2">
        <div class="flex items-center justify-between">
          <span>Backend</span>
          <span class="text-green-500 font-medium">{{ health.backend?.status || 'unknown' }}</span>
        </div>
      </div>
    </div>

    <NuxtLink to="/" class="text-blue-500 hover:underline">
      Back to Home
    </NuxtLink>
  </div>
</template>
