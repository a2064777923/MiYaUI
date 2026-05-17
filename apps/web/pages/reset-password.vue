<script setup lang="ts">
const form = reactive({
  email_or_username: '',
})
const result = ref<any | null>(null)

async function submit() {
  result.value = await $fetch('/api/auth/reset-password', {
    method: 'POST',
    body: form,
  })
}
</script>

<template>
  <div class="miya-shell" style="padding: 48px 0;">
    <form class="miya-card" style="max-width: 520px; margin: 0 auto; padding: 32px; display: grid; gap: 16px;" @submit.prevent="submit">
      <h1 style="margin: 0;">重置密码</h1>
      <label style="display: grid; gap: 8px;">
        <span>邮箱或用户名</span>
        <input v-model="form.email_or_username" type="text" style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--miya-border);">
      </label>
      <button type="submit" style="padding: 14px 18px; border-radius: 16px; border: 0; background: var(--miya-accent); color: white;">
        获取重置入口
      </button>
      <a v-if="result?.redirect_url" :href="result.redirect_url" class="miya-muted">
        跳转到 WordPress 找回密码
      </a>
    </form>
  </div>
</template>
