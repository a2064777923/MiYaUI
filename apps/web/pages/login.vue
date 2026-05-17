<script setup lang="ts">
const { login } = useSession()
const form = reactive({
  username_or_email: '',
  password: '',
})
const errorMessage = ref('')
const socialMessage = ref('')

const socialProviders = [
  { id: 'wechat', label: 'WeChat' },
  { id: 'qq', label: 'QQ' },
  { id: 'weibo', label: 'Weibo' },
  { id: 'baidu', label: 'Baidu' },
  { id: 'google', label: 'Google' },
]

async function submit() {
  errorMessage.value = ''
  try {
    await login(form.username_or_email, form.password)
    await navigateTo('/')
  }
  catch (error: any) {
    errorMessage.value = error?.data?.message || error?.statusMessage || '登录失败'
  }
}

async function startSocial(provider: string) {
  socialMessage.value = ''
  try {
    const result = await $fetch<{ authorization_url: string }>(`/api/auth/social/${provider}`)
    await navigateTo(result.authorization_url, { external: true })
  }
  catch (error: any) {
    socialMessage.value = error?.data?.message || error?.statusMessage || '社交登录暂不可用'
  }
}
</script>

<template>
  <div class="miya-shell" style="padding: 48px 0;">
    <form class="miya-card" style="max-width: 520px; margin: 0 auto; padding: 32px; display: grid; gap: 16px;" @submit.prevent="submit">
      <div>
        <p class="miya-muted" style="margin: 0 0 8px;">Phase 2 Auth Bridge</p>
        <h1 style="margin: 0;">登录 MiyaUI</h1>
      </div>
      <label style="display: grid; gap: 8px;">
        <span>用户名或邮箱</span>
        <input v-model="form.username_or_email" type="text" style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--miya-border);">
      </label>
      <label style="display: grid; gap: 8px;">
        <span>密码</span>
        <input v-model="form.password" type="password" style="padding: 12px 14px; border-radius: 14px; border: 1px solid var(--miya-border);">
      </label>
      <p v-if="errorMessage" style="margin: 0; color: #a73920;">{{ errorMessage }}</p>
      <button type="submit" style="padding: 14px 18px; border-radius: 16px; border: 0; background: var(--miya-accent); color: white;">
        登录
      </button>
      <div style="display: grid; gap: 10px;">
        <p class="miya-muted" style="margin: 0;">社交登录入口</p>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
          <button
            v-for="provider in socialProviders"
            :key="provider.id"
            type="button"
            :data-provider="provider.id"
            style="padding: 10px 12px; border-radius: 999px; border: 1px solid var(--miya-border); background: white;"
            @click="startSocial(provider.id)"
          >
            {{ provider.label }}
          </button>
        </div>
        <p v-if="socialMessage" style="margin: 0; color: #a73920;">{{ socialMessage }}</p>
      </div>
    </form>
  </div>
</template>
