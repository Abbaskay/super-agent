import { createAuthStore } from '@shared/auth.js'

export const authState = createAuthStore('superagent_auth')

export function socialAuth() {
  const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  return {
    googleRedirect() {
      console.log('[SocialAuth] Google redirect')
      window.location.href = `${API_BASE}/api/auth/google/redirect`
    },
  }
}

// Wrap authState login/logout with logging
const origLogin = authState.login.bind(authState)
authState.login = (token, user) => {
  console.log('[Auth] login', { userId: user?.id, email: user?.email })
  origLogin(token, user)
}

const origLogout = authState.logout.bind(authState)
authState.logout = () => {
  console.log('[Auth] logout', { wasAuthenticated: !!authState.token })
  origLogout()
}
