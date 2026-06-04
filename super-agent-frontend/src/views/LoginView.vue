<template>
  <div class="auth-page">
    <div class="auth-container">
      <router-link :to="{ name: 'Welcome' }" class="auth-back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back
      </router-link>

      <div class="auth-header">
        <div class="auth-logo">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
            <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
          </svg>
        </div>
        <h1 class="auth-title">Welcome back</h1>
        <p class="auth-subtitle">Sign in to your Zonrad account</p>
      </div>

      <form class="auth-form" @submit.prevent="handleLogin">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input v-model="email" type="email" class="form-input" placeholder="you@example.com" required autocomplete="email">
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input v-model="password" type="password" class="form-input" placeholder="Enter your password" required autocomplete="current-password">
        </div>
        <div v-if="error" class="form-error">{{ error }}</div>
        <button type="submit" class="form-submit" :disabled="loading">
          {{ loading ? 'Signing in...' : 'Sign in' }}
        </button>
      </form>

      <div class="auth-divider">
        <span>or continue with</span>
      </div>

      <div class="social-buttons">
        <button class="social-btn google-only" @click="socialAuth().googleRedirect()">
          <svg width="18" height="18" viewBox="0 0 24 24"><path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
          Continue with Google
        </button>
      </div>

      <p class="auth-footer">
        Don't have an account? <router-link :to="{ name: 'Signup' }">Sign up</router-link>
      </p>
    </div>
  </div>
</template>

<script>
import { authState, socialAuth } from '../store/auth.js'
import { authAPI } from '../services/api.js'

export default {
  name: 'LoginView',
  data() {
    return {
      email: '',
      password: '',
      loading: false,
      error: '',
    }
  },
  methods: {
    socialAuth,
    async handleLogin() {
      console.log('[LoginView] handleLogin', { email: this.email })
      this.loading = true
      this.error = ''
      try {
        const res = await authAPI().login(this.email, this.password)
        console.log('[LoginView] login successful', { userId: res.data.user?.id })
        authState.login(res.data.token, res.data.user)
      } catch (e) {
        console.error('[LoginView] login failed', { error: e.response?.data?.message || e.message })
        this.error = e.response?.data?.message || 'Login failed. Is the backend running?'
        this.loading = false
        return
      }
      const redirect = this.$route.query.redirect || '/'
      console.log('[LoginView] redirecting to', redirect)
      this.$router.push(redirect)
    },
  },
}
</script>

<style scoped>
.auth-page {
  height: 100dvh; display: flex; align-items: center; justify-content: center;
  background: var(--bg-primary); padding: 20px;
}
.auth-container {
  width: 100%; max-width: 400px; position: relative;
}
.auth-back {
  display: inline-flex; align-items: center; gap: 5px; font-size: 13px;
  color: var(--text-tertiary); text-decoration: none; margin-bottom: 24px;
  transition: color 0.15s;
}
.auth-back:hover { color: var(--text-secondary); }
.auth-header { text-align: center; margin-bottom: 32px; }
.auth-logo {
  width: 48px; height: 48px; border-radius: var(--radius-lg); margin: 0 auto 16px;
  background: linear-gradient(135deg, rgba(94,158,255,0.15), rgba(94,92,230,0.15));
  border: 1px solid rgba(94,158,255,0.1);
  display: flex; align-items: center; justify-content: center; color: var(--accent-blue);
}
.auth-title { font-size: 28px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 8px; color: var(--text-primary); }
.auth-subtitle { font-size: 15px; color: var(--text-tertiary); }
.auth-form { display: flex; flex-direction: column; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 13px; font-weight: 500; color: var(--text-secondary); }
.form-input {
  padding: 12px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-default);
  background: var(--bg-secondary); color: var(--text-primary); font-size: 15px;
  font-family: inherit; outline: none; transition: border-color 0.2s;
}
.form-input:focus { border-color: rgba(94,158,255,0.3); }
.form-input::placeholder { color: var(--text-tertiary); }
.form-error { font-size: 13px; color: var(--accent-red); padding: 8px 12px; background: rgba(255,69,58,0.06); border-radius: var(--radius-sm); }
.form-submit {
  padding: 12px; border-radius: var(--radius-md); border: none;
  background: var(--accent-blue); color: white; font-size: 15px; font-weight: 600;
  cursor: pointer; transition: all 0.15s; font-family: inherit;
}
.form-submit:hover { background: #4a8be7; transform: translateY(-1px); }
.form-submit:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
.auth-divider {
  display: flex; align-items: center; gap: 12px; margin: 24px 0;
  font-size: 12px; color: var(--text-tertiary);
}
.auth-divider::before, .auth-divider::after { content: ''; flex: 1; height: 1px; background: var(--border-subtle); }
.social-buttons { display: flex; gap: 10px; }
.social-btn {
  flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
  padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border-default);
  background: var(--bg-secondary); color: var(--text-secondary); font-size: 13px;
  font-weight: 500; cursor: pointer; transition: all 0.15s; font-family: inherit;
}
.social-btn:hover { background: var(--bg-glass-hover); color: var(--text-primary); border-color: var(--border-hover); }
.social-btn.google-only { flex: none; width: 100%; }
.auth-footer { text-align: center; margin-top: 24px; font-size: 13px; color: var(--text-tertiary); }
.auth-footer a { color: var(--accent-blue); text-decoration: none; font-weight: 500; }
.auth-footer a:hover { text-decoration: underline; }
</style>
