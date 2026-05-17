<script setup lang="ts">
const form = reactive({
  username: '',
  email: '',
})
const result = ref<any | null>(null)

async function submit() {
  result.value = await $fetch('/api/auth/register', {
    method: 'POST',
    body: form,
  })
}
</script>

<template>
  <div class="miya-shell" style="padding: 48px 0;">
    <form class="miya-card" style="max-width: 520px; margin: 0 auto; padding: 32px; display: grid; gap: 16px;" @submit.prevent="submit">
      <h1 style="margin: 0;">注册入口</h1>
      <label style="display: grid; gap: 8px;">
        <span>用户名</span>
        <input v-model="form.username" type="text" style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--miya-border);">
      </label>
      <label style="display: grid; gap: 8px;">
        <span>邮箱</span>
        <input v-model="form.email" type="email" style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--miya-border);">
      </label>
      <button type="submit" style="padding: 14px 18px; border-radius: 16px; border: 0; background: var(--miya-accent); color: white;">
        继续
      </button>
      <a v-if="result?.redirect_url" :href="result.redirect_url" class="miya-muted">
        跳转到 WordPress 完成注册
      </a>
    </form>
  </div>
</template>
