<script setup lang="ts">
const { data: health, status, error } = useFetch('/api/health')
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center gap-6 p-8">
    <h1 class="text-4xl font-bold">MiyaUI</h1>
    <p class="text-lg text-gray-500">Premium Content Platform</p>

    <div class="mt-8 p-6 rounded-lg border max-w-md w-full">
      <h2 class="text-xl font-semibold mb-4">System Status</h2>

      <div v-if="status === 'pending'" class="text-gray-400">
        Loading health status...
      </div>

      <div v-else-if="error" class="text-red-500">
        Backend unavailable: {{ error.message }}
      </div>

      <div v-else-if="health" class="space-y-2">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded-full bg-green-500" />
          <span>Frontend: OK</span>
        </div>
        <div class="flex items-center gap-2">
          <span
            class="w-3 h-3 rounded-full"
            :class="health.backend?.status === 'ok' ? 'bg-green-500' : 'bg-red-500'"
          />
          <span>Backend: {{ health.backend?.status || 'unknown' }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
