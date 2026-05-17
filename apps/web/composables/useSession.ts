export function useSession() {
  const session = useState<any | null>('miya-session', () => null)
  const pending = useState<boolean>('miya-session-pending', () => false)

  async function refresh() {
    pending.value = true
    try {
      session.value = await $fetch('/api/auth/session')
    }
    finally {
      pending.value = false
    }
    return session.value
  }

  async function login(username_or_email: string, password: string) {
    const result = await $fetch('/api/auth/login', {
      method: 'POST',
      body: { username_or_email, password },
    })
    session.value = result
    return result
  }

  async function logout() {
    await $fetch('/api/auth/logout', { method: 'POST' })
    session.value = null
  }

  return {
    session,
    pending,
    refresh,
    login,
    logout,
  }
}
