<template>
  <div class="callback-page">
    <div class="callback-spinner"></div>
    <p>Completing sign in...</p>
  </div>
</template>

<script>
import { authState } from '../store/auth.js'

export default {
  name: 'AuthCallbackView',
  mounted() {
    console.log('[AuthCallback] mounted', { hasOpener: !!window.opener })
    const params = new URLSearchParams(window.location.search)
    const token = params.get('token')
    const userRaw = params.get('user')
    console.log('[AuthCallback] params', { hasToken: !!token, hasUser: !!userRaw })

    if (token && userRaw) {
      try {
        const user = JSON.parse(decodeURIComponent(userRaw))
        console.log('[AuthCallback] login with social user', { userId: user.id, email: user.email })
        authState.login(token, user)
        // If opened as a popup (Google OAuth), send result to opener and close
        if (window.opener) {
          console.log('[AuthCallback] posting message to opener and closing')
          window.opener.postMessage({ type: 'auth_callback', token, user }, '*')
          window.close()
          return
        }
        console.log('[AuthCallback] redirecting to /')
        this.$router.push('/')
      } catch (e) {
        console.error('[AuthCallback] error parsing user data', { error: e.message })
        window.opener ? window.close() : this.$router.push('/login')
      }
    } else {
      console.warn('[AuthCallback] missing token or user')
      window.opener ? window.close() : this.$router.push('/login')
    }
  },
}
</script>

<style scoped>
.callback-page {
  height: 100dvh; display: flex; flex-direction: column; align-items: center;
  justify-content: center; gap: 16px; background: var(--bg-primary);
  color: var(--text-secondary); font-size: 15px;
}
.callback-spinner {
  width: 32px; height: 32px; border-radius: 50%;
  border: 3px solid var(--border-subtle);
  border-top-color: var(--accent-blue);
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
