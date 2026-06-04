<template>
  <div class="app-shell">
    <!-- ── GUEST LANDING ── -->
    <template v-if="!isAuthenticated">
      <header class="app-header">
        <div class="header-left">
          <div class="header-logo">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2l7 4v8l-7 4-7-4V6z"/>
            </svg>
          </div>
          <span class="header-title">Zonrad</span>
        </div>
        <div class="header-right">
          <router-link :to="{ name: 'Login' }" class="header-link">Sign in</router-link>
          <router-link :to="{ name: 'Signup' }" class="header-btn-primary">Get Started</router-link>
        </div>
      </header>
      <main class="guest-main">
        <div class="guest-hero">
          <div class="guest-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2l7 4v8l-7 4-7-4V6z"/>
            </svg>
          </div>
          <h1 class="guest-title">Welcome to Zonrad</h1>
          <p class="guest-subtitle">Your intelligent AI workspace. Chat, create documents, build presentations, and verify facts — all in one place.</p>
          <div class="guest-actions">
            <router-link :to="{ name: 'Signup' }" class="guest-btn-primary">Get Started for Free</router-link>
            <router-link :to="{ name: 'Login' }" class="guest-btn-secondary">Sign In</router-link>
          </div>
          <div class="guest-admin">
            <button class="admin-login-btn" @click="adminLogin">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              Admin Demo Login
            </button>
          </div>

        </div>
      </main>
    </template>

    <!-- ── AUTH DASHBOARD ── -->
    <template v-else>
      <header class="app-header">
        <div class="header-left">
          <div class="header-logo">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2l7 4v8l-7 4-7-4V6z"/>
            </svg>
          </div>
          <span class="header-title">Zonrad</span>
          <div class="header-divider"></div>
          <span class="header-breadcrumb">Dashboard</span>
        </div>
        <div class="header-right">
<button class="header-btn" @click="goToWorkspace" title="Chat workspace">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
          </button>
          <div class="user-menu" ref="userMenu">
            <button @click="userMenuOpen = !userMenuOpen" class="user-trigger">
              <span class="user-avatar">{{ userInitial }}</span>
            </button>
            <Transition name="menu">
              <div class="user-dropdown" v-if="userMenuOpen" @click.stop>
                <div class="ud-user">
                  <span class="ud-name">{{ authState.user?.name || 'User' }}</span>
                  <span class="ud-email">{{ authState.user?.email || '' }}</span>
                </div>
                <router-link :to="{ name: 'Pricing' }" class="ud-item" @click="userMenuOpen = false">Pricing & Plan</router-link>
                <div class="ud-divider"></div>
                <button class="ud-item ud-logout" @click="handleLogout">Sign out</button>
              </div>
            </Transition>
          </div>
        </div>
      </header>

      <main class="app-main">
        <div class="dashboard">
          <div class="dashboard-hero">
            <h1 class="dashboard-title">What can I help you with, {{ displayName }}?</h1>
            <p class="dashboard-subtitle">Choose an agent or type a message to get started.</p>
          </div>

          <div class="agent-cards-row">
            <div class="agent-card" @click="launchAgent('docs')" data-accent="blue" title="Open AI Docs Agent">
              <div class="agent-card-icon blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
              </div>
              <div class="agent-card-text">
                <span class="agent-card-label">AI Docs</span>
              </div>
            </div>
            <div class="agent-card" @click="launchAgent('slides')" data-accent="amber" title="Open AI Slides Agent">
              <div class="agent-card-icon amber">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
              </div>
              <div class="agent-card-text">
                <span class="agent-card-label">AI Slides</span>              </div>
            </div>
            <div class="agent-card" @click="launchAgent('factcheck')" data-accent="emerald" title="Open AI Fact Checker">
              <div class="agent-card-icon emerald">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
              </div>
              <div class="agent-card-text">
                <span class="agent-card-label">AI Fact Checker</span>              </div>
            </div>
            <div class="agent-card" @click="launchAgent('excel')" data-accent="excel" title="Open AI Excel Agent">
              <div class="agent-card-icon excel">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
              </div>
              <div class="agent-card-text">
                <span class="agent-card-label">AI Excel</span>              </div>
            </div>
          </div>

          <div class="dashboard-input">
            <div class="dashboard-input-inner">
              <button class="dashboard-attach-btn" @click="$refs.dashFileInput.click()" title="Attach file or image">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
              </button>
              <input ref="dashFileInput" type="file" accept=".pdf,.docx,.txt,.png,.jpg,.jpeg,.gif,.webp" @change="onDashFileUpload" hidden>
              <div class="dashboard-input-wrap">
                <div v-if="dashUploadFilename" class="dash-upload-badge">
                  <img v-if="dashUploadIsImage" :src="dashUploadDataUrl" class="dash-upload-thumb" alt="">
                  <span class="dash-upload-name">{{ dashUploadFilename }}</span>
                  <button class="dash-upload-remove" @click="dashClearUpload">✕</button>
                </div>
                <input v-model="inputText" type="text" class="dashboard-input-field" :placeholder="dashUploadFilename ? 'Ask about this file...' : 'Ask anything...'" @keydown.enter.prevent="goToWorkspaceWith(inputText)">
              </div>
              <button class="dashboard-input-btn" :disabled="!canSend" @click="goToWorkspaceWith(inputText)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              </button>
            </div>
          </div>
        </div>
      </main>
    </template>
  </div>
</template>

<script>
import { authState } from '../store/auth.js'
import { authAPI } from '../services/api.js'

const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000'

export default {
  name: 'WelcomeView',
  data() {
    return {
      inputText: '',
      authState,
      userMenuOpen: false,
      dashUploadDataUrl: '',
      dashUploadFilename: '',
      dashUploadIsImage: false,
    }
  },
  computed: {
    isAuthenticated() { return authState.isAuthenticated },
    userInitial() { return (authState.user?.name || 'U')[0].toUpperCase() },
    canSend() { return !!(this.inputText.trim() || this.dashUploadDataUrl) },
    displayName() {
      const name = authState.user?.name || ''
      if (name) return name
      const email = authState.user?.email || ''
      const atIdx = email.indexOf('@')
      return atIdx > 0 ? email.slice(0, atIdx) : 'there'
    },
  },
  methods: {
    goToWorkspace() {
      console.log('[WelcomeView] goToWorkspace')
      this.$router.push({ name: 'Workspace' })
    },
    goToWorkspaceWith(text) {
      const msg = (text || this.inputText).trim()
      if (!msg && !this.dashUploadDataUrl) {
        console.warn('[WelcomeView] goToWorkspaceWith: empty')
        return
      }
      console.log('[WelcomeView] goToWorkspaceWith', { messagePreview: msg.slice(0, 80), hasFile: !!this.dashUploadDataUrl })

      // Store pending upload in sessionStorage for WorkspaceView to pick up
      if (this.dashUploadDataUrl) {
        sessionStorage.setItem('pendingUpload', JSON.stringify({
          filename: this.dashUploadFilename,
          dataUrl: this.dashUploadDataUrl,
          isImage: this.dashUploadIsImage,
        }))
      }

      this.inputText = ''
      this.dashClearUpload()
      this.$router.push({ name: 'Workspace', query: { prompt: msg } })
    },
    onDashFileUpload(e) {
      const f = e.target.files?.[0]
      if (!f) return
      const ext = f.name.split('.').pop().toLowerCase()
      const imageExts = ['png', 'jpg', 'jpeg', 'gif', 'webp']
      this.dashUploadFilename = f.name
      this.dashUploadIsImage = imageExts.includes(ext)
      const reader = new FileReader()
      reader.onload = (ev) => {
        this.dashUploadDataUrl = ev.target.result
        console.log('[WelcomeView] file read', { name: f.name, isImage: this.dashUploadIsImage })
      }
      reader.onerror = () => {
        console.error('[WelcomeView] file read failed')
        this.dashClearUpload()
      }
      reader.readAsDataURL(f)
      e.target.value = ''
    },
    dashClearUpload() {
      this.dashUploadDataUrl = ''
      this.dashUploadFilename = ''
      this.dashUploadIsImage = false
    },
    launchAgent(agent) {
      console.log('[WelcomeView] launchAgent', { agent })
      const BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'
      window.open(`${BASE_URL}/sso/${agent}`, '_blank', 'noopener,noreferrer')
    },
    async adminLogin() {
      console.log('[WelcomeView] adminLogin')
      // First try to log in (admin is DB-seeded via AdminUserSeeder)
      try {
        const res = await authAPI().login('admin@superagent.com', 'admin123')
        console.log('[WelcomeView] admin login successful')
        authState.login(res.data.token, res.data.user)
        this.$router.push('/workspace')
        return
      } catch (e) {
        console.warn('[WelcomeView] admin login failed — admin may not be seeded yet, trying register', { error: e.response?.data?.message || e.message })
      }

      // If login failed (admin not in DB), try registering the admin user
      try {
        const res = await authAPI().register('Admin', 'admin@superagent.com', 'admin123')
        console.log('[WelcomeView] admin registration successful')
        authState.login(res.data.token, res.data.user)
        this.$router.push('/workspace')
        return
      } catch (e) {
        const msg = e.response?.data?.message
          || (e.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(', ') : null)
          || e.message
          || 'Admin login failed. Check that the backend is running and DB is migrated.'
        console.error('[WelcomeView] admin login failed', { error: msg })
        alert(msg)
      }
    },
    handleLogout() {
      console.log('[WelcomeView] handleLogout')
      this.userMenuOpen = false
      authState.logout()
    },
    handleClickOutside(e) {
      if (this.$refs.userMenu && !this.$refs.userMenu.contains(e.target)) {
        this.userMenuOpen = false
      }
    },
  },
  mounted() {
    console.log('[WelcomeView] mounted', { isAuthenticated: authState.isAuthenticated, user: authState.user?.email })
    document.addEventListener('click', this.handleClickOutside)
  },
  beforeUnmount() {
    console.log('[WelcomeView] beforeUnmount')
    document.removeEventListener('click', this.handleClickOutside)
  },
}
</script>

<style>
:root {
  --zp-accent: #0095ff;
  --zp-accent-light: #40bfff;
  --zp-accent-dark: #0066cc;
  --zp-glow: rgba(0, 149, 255, 0.12);
  --zp-glow-strong: rgba(0, 149, 255, 0.25);
  --zp-bg: #070b15;
  --zp-bg-elevated: #0b1020;
  --zp-glass: rgba(0, 149, 255, 0.04);
  --zp-glass-hover: rgba(0, 149, 255, 0.08);
  --zp-glass-border: rgba(0, 149, 255, 0.08);
  --zp-glass-border-hover: rgba(0, 149, 255, 0.16);
  --zp-text: #f0f0f5;
  --zp-text-secondary: #a0a0b0;
  --zp-text-tertiary: #6b6b7b;
}
</style>

<style scoped>
.app-shell { height: 100dvh; display: flex; flex-direction: column; overflow: hidden; background: var(--zp-bg); position: relative; }
/* ── SHARED HEADER ── */
.app-header {
  height: var(--zp-header-h); flex-shrink: 0; display: flex; align-items: center;
  justify-content: space-between; padding: 0 20px;
  background: rgba(5, 5, 8, 0.78); backdrop-filter: blur(28px) saturate(1.3);
  border-bottom: 1px solid var(--zp-glass-border); z-index: 100; user-select: none;
}
.header-left { display: flex; align-items: center; gap: 10px; }
.header-logo {
  width: 30px; height: 30px; border-radius: 8px;
  background: linear-gradient(135deg, var(--zp-accent-dark), var(--zp-accent));
  display: flex; align-items: center; justify-content: center;
  color: white; flex-shrink: 0; box-shadow: 0 0 16px var(--zp-glow);
}
.header-title { font-size: 15px; font-weight: 700; letter-spacing: -0.01em; color: var(--zp-text); }
.header-divider { width: 1px; height: 18px; background: var(--zp-glass-border); flex-shrink: 0; }
.header-breadcrumb { font-size: 13px; color: var(--zp-text-tertiary); font-weight: 400; }
.header-right { display: flex; align-items: center; gap: 8px; }
.header-link {
  font-size: 13px; color: var(--zp-text-secondary); text-decoration: none; padding: 6px 14px;
  border-radius: 999px; transition: all 0.15s;
}
.header-link:hover { color: var(--zp-text); background: var(--zp-glass); }
.header-btn-primary {
  font-size: 13px; font-weight: 600; color: white; text-decoration: none; padding: 6px 18px;
  border-radius: 999px; background: linear-gradient(135deg, var(--zp-accent), var(--zp-accent-dark));
  transition: all 0.2s; box-shadow: 0 0 12px var(--zp-glow);
}
.header-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 0 20px var(--zp-glow-strong); }
.header-btn {
  width: 32px; height: 32px; border-radius: 8px; border: none;
  background: transparent; color: var(--zp-text-tertiary); cursor: pointer;
  display: flex; align-items: center; justify-content: center; transition: all 0.15s;
}
.header-btn:hover { background: var(--zp-glass-hover); color: var(--zp-text-secondary); }
.header-btn:active { transform: scale(0.92); }

/* ── GUEST LANDING ── */
.guest-main {
  flex: 1; display: flex; align-items: center; justify-content: center;
  padding: 40px 24px; overflow-y: auto; position: relative;
  background-image: radial-gradient(rgba(0, 149, 255, 0.12) 1.5px, transparent 1.5px);
  background-size: 80px 80px;
  background-color: var(--zp-bg);
}
.guest-hero { text-align: center; max-width: 540px; position: relative; z-index: 1; }
.guest-hero::before {
  content: ''; position: absolute; top: 50%; left: 50%; width: 500px; height: 500px;
  transform: translate(-50%, -60%); pointer-events: none;
  background: radial-gradient(circle, rgba(0, 149, 255, 0.1) 0%, transparent 60%);
  animation: orbFloat 8s ease-in-out infinite;
}
.guest-hero::after {
  content: ''; position: absolute; top: 50%; left: 50%; width: 700px; height: 700px;
  transform: translate(-50%, -55%); pointer-events: none;
  background: radial-gradient(circle, rgba(0, 149, 255, 0.08) 0%, transparent 50%);
  animation: orbFloat 10s ease-in-out infinite 1s;
}
.guest-icon {
  width: 72px; height: 72px; border-radius: 18px; margin: 0 auto 28px;
  background: linear-gradient(135deg, var(--zp-accent-dark), var(--zp-accent));
  display: flex; align-items: center; justify-content: center;
  color: white; box-shadow: 0 0 30px var(--zp-glow);
  position: relative;
}
.guest-icon::after {
  content: ''; position: absolute; inset: -3px; border-radius: 21px;
  background: linear-gradient(135deg, var(--zp-accent), transparent 60%);
  opacity: 0.3; z-index: -1;
}
.guest-title {
  font-family: var(--zp-font-display); font-size: 48px; font-weight: 800; letter-spacing: -0.04em; line-height: 1.05;
  margin-bottom: 16px;
  background: linear-gradient(135deg, var(--zp-text) 15%, var(--zp-accent-light) 40%, #ffffff 47%, var(--zp-accent) 50%, #ffffff 53%, var(--zp-accent-light) 60%, var(--zp-text) 85%);
  background-size: 300% 100%;
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: headingFadeIn 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards,
             headingShimmer 5.5s ease-in-out infinite 0.7s;
}

.guest-subtitle { font-size: 17px; color: var(--zp-text-tertiary); line-height: 1.6; margin-bottom: 36px; }
.guest-actions { display: flex; gap: 12px; justify-content: center; margin-bottom: 32px; }
.guest-btn-primary {
  padding: 13px 32px; border-radius: 999px;
  background: linear-gradient(135deg, var(--zp-accent), var(--zp-accent-dark));
  color: white; font-size: 15px; font-weight: 600; text-decoration: none;
  transition: all 0.2s; box-shadow: 0 0 16px var(--zp-glow);
}
.guest-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 0 28px var(--zp-glow-strong); }
.guest-btn-secondary {
  padding: 13px 32px; border-radius: 999px;
  border: 1px solid var(--zp-glass-border);
  background: var(--zp-glass);
  color: var(--zp-text-secondary); font-size: 15px; font-weight: 500; text-decoration: none;
  transition: all 0.2s;
}
.guest-btn-secondary:hover { background: var(--zp-glass-hover); color: var(--zp-text); border-color: var(--zp-glass-border-hover); }
.guest-admin { margin-top: 8px; display: flex; justify-content: center; }
.admin-login-btn {
  display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px;
  border-radius: 999px; border: 1px dashed rgba(0, 149, 255, 0.12);
  background: transparent; color: var(--zp-text-tertiary); font-size: 11px;
  cursor: pointer; transition: all 0.15s; font-family: inherit;
}
.admin-login-btn:hover { border-color: rgba(0, 149, 255, 0.2); color: var(--zp-accent-light); background: var(--zp-glass); }

/* ── AUTH DASHBOARD ── */
.app-main { flex: 1; min-height: 0; display: flex; flex-direction: column; overflow: hidden; position: relative;
  background-image: radial-gradient(rgba(0, 149, 255, 0.12) 1.5px, transparent 1.5px);
  background-size: 80px 80px;
  background-color: var(--zp-bg);
}
.dashboard {
  flex: 1; display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 32px 24px; overflow-y: auto; position: relative; z-index: 1;
}
.dashboard-hero { text-align: center; margin-bottom: 32px; position: relative; }
.dashboard-hero::before {
  content: ''; position: absolute; top: 50%; left: 50%; width: 500px; height: 500px;
  transform: translate(-50%, -60%); pointer-events: none;
  background: radial-gradient(circle, rgba(0, 149, 255, 0.1) 0%, transparent 60%);
  animation: orbFloat 8s ease-in-out infinite;
}
.dashboard-title {
  font-size: 38px; font-weight: 750; letter-spacing: -0.03em; margin-bottom: 10px;
  background: linear-gradient(135deg, var(--zp-text) 15%, var(--zp-accent-light) 40%, #ffffff 47%, var(--zp-accent) 50%, #ffffff 53%, var(--zp-accent-light) 60%, var(--zp-text) 85%);
  background-size: 300% 100%;
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: headingFadeIn 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards,
             headingShimmer 5.5s ease-in-out infinite 0.7s;
}
.dashboard-subtitle { font-size: 16px; color: var(--zp-text-tertiary); }

.agent-cards-row { display: flex; align-items: stretch; gap: 12px; width: 100%; max-width: 700px; margin-bottom: 36px; }
.agent-card {
  display: flex; flex-direction: row; align-items: center; gap: 14px; flex: 1; min-width: 0;
  padding: 16px 20px; border-radius: 12px;
  background: #10101a; border: 1px solid rgba(255,255,255,0.06);
  cursor: pointer; position: relative; overflow: hidden;
  transition: all 0.25s ease;
}
.agent-card::before {
  content: ''; position: absolute; left: 0; top: 20%; bottom: 20%; width: 2px;
  border-radius: 0 2px 2px 0; opacity: 0;
  transition: all 0.25s ease; pointer-events: none;
}
.agent-card[data-accent="blue"]::before { background: #4682b4; }
.agent-card[data-accent="amber"]::before { background: #D04423; }
.agent-card[data-accent="emerald"]::before { background: #f0a030; }
.agent-card[data-accent="excel"]::before { background: #21a366; }
.agent-card:hover::before { opacity: 0.6; top: 6px; bottom: 6px; }
.agent-card:hover { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.1); }
.agent-card:active { transform: scale(0.98); }
.agent-card-icon {
  width: 44px; height: 44px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.25s; flex-shrink: 0; position: relative;
}
.agent-card-icon.blue { background: rgba(70,130,180,0.12); border: 1px solid rgba(70,130,180,0.15); }
.agent-card-icon.blue svg { color: #4682b4; }
.agent-card-icon.amber { background: rgba(208,68,35,0.12); border: 1px solid rgba(208,68,35,0.15); }
.agent-card-icon.amber svg { color: #D04423; }
.agent-card-icon.emerald { background: rgba(240,160,48,0.12); border: 1px solid rgba(240,160,48,0.15); }
.agent-card-icon.emerald svg { color: #f0a030; }
.agent-card-icon.excel { background: rgba(33,163,102,0.12); border: 1px solid rgba(33,163,102,0.15); }
.agent-card-icon.excel svg { color: #21a366; }
.agent-card:hover .agent-card-icon { transform: scale(1.05); }
.agent-card-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.agent-card-label { font-size: 14px; font-weight: 600; color: var(--zp-text); }

.dashboard-input { width: 100%; max-width: 680px; }
.dashboard-input-inner {
  display: flex; align-items: center; gap: 4px;
  background: rgba(16, 16, 26, 0.8); border: 1px solid var(--zp-glass-border);
  border-radius: 16px; padding: 4px;
  transition: border-color 0.2s, box-shadow 0.2s;
  backdrop-filter: blur(12px); box-shadow: 0 4px 24px rgba(0,0,0,0.3);
}
.dashboard-input-inner:focus-within { border-color: rgba(0, 149, 255, 0.2); box-shadow: 0 4px 32px rgba(0,0,0,0.35), 0 0 0 1px rgba(0, 149, 255, 0.06); }
.dashboard-input-field {
  width: 100%; background: transparent; border: none; outline: none;
  color: var(--zp-text); font-size: 16px; font-weight: 400; padding: 12px 0;
  font-family: inherit; line-height: 1.4;
}
.dashboard-input-field::placeholder { color: var(--zp-text-tertiary); }
.dashboard-input-btn {
  width: 40px; height: 40px; border-radius: 999px; border: none;
  background: linear-gradient(135deg, var(--zp-accent), var(--zp-accent-dark));
  color: white; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s; box-shadow: 0 0 12px var(--zp-glow); flex-shrink: 0;
}
.dashboard-input-btn:hover { transform: scale(1.04); box-shadow: 0 0 20px var(--zp-glow-strong); }
.dashboard-input-btn:active { transform: scale(0.92); }
.dashboard-input-btn:disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; }
.dashboard-attach-btn {
  width: 34px; height: 34px; border-radius: 999px; border: none;
  background: transparent; color: var(--zp-text-tertiary); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.15s; flex-shrink: 0;
}
.dashboard-attach-btn:hover { background: var(--zp-glass-hover); color: var(--zp-text-secondary); }
.dashboard-attach-btn:active { transform: scale(0.9); }
.dashboard-input-wrap { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.dash-upload-badge {
  display: flex; align-items: center; gap: 6px; padding: 3px 0 0;
  font-size: 12px; color: var(--zp-accent-light);
}
.dash-upload-thumb {
  width: 28px; height: 28px; border-radius: 4px; object-fit: cover;
  border: 1px solid var(--zp-glass-border); flex-shrink: 0;
}
.dash-upload-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.dash-upload-remove {
  background: none; border: none; color: var(--zp-text-tertiary); cursor: pointer;
  font-size: 12px; padding: 0 2px; line-height: 1; flex-shrink: 0;
}
.dash-upload-remove:hover { color: #ff453a; }

.user-menu { position: relative; }
.user-trigger {
  width: 30px; height: 30px; border-radius: 50%; border: none;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, var(--zp-accent-dark), var(--zp-accent));
  color: white; font-size: 12px; font-weight: 600; box-shadow: 0 0 8px var(--zp-glow);
}
.user-avatar { line-height: 1; }
.user-dropdown {
  position: absolute; top: calc(100% + 8px); right: 0; min-width: 200px;
  background: rgba(10, 10, 18, 0.98); backdrop-filter: blur(24px);
  border: 1px solid var(--zp-glass-border); border-radius: 12px;
  padding: 8px; z-index: 60; box-shadow: 0 16px 48px rgba(0,0,0,0.6);
}
.ud-user { padding: 4px 8px 8px; }
.ud-name { display: block; font-size: 14px; font-weight: 600; color: var(--zp-text); }
.ud-email { display: block; font-size: 12px; color: var(--zp-text-tertiary); margin-top: 2px; }
.ud-divider { height: 1px; background: var(--zp-glass-border); margin: 4px 0; }
.ud-item {
  display: block; width: 100%; padding: 7px 8px; border-radius: 5px;
  font-size: 13px; color: var(--zp-text-secondary); text-decoration: none;
  cursor: pointer; transition: all 0.1s; border: none; background: none; text-align: left; font-family: inherit;
}
.ud-item:hover { background: var(--zp-glass); color: var(--zp-text); }
.ud-logout:hover { color: #ff453a; }

.menu-enter-active, .menu-leave-active { transition: all 0.15s; }
.menu-enter-from, .menu-leave-to { opacity: 0; transform: translateY(-5px) scale(0.96); }

@keyframes headingFadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes headingShimmer {
  0% { background-position: 100% 50%; }
  50% { background-position: 0% 50%; }
  100% { background-position: 100% 50%; }
}

@keyframes orbFloat {
  0%, 100% { transform: translate(-50%, -60%) scale(1); opacity: 0.5; }
  33% { transform: translate(-45%, -65%) scale(1.05); opacity: 0.7; }
  66% { transform: translate(-55%, -55%) scale(0.95); opacity: 0.4; }
}

@keyframes iconPulse {
  0% { transform: scale(1); opacity: 0.6; }
  50% { transform: scale(1.12); opacity: 0.2; }
  100% { transform: scale(1); opacity: 0.6; }
}

@keyframes iconBounce {
  0% { transform: translateY(0); }
  30% { transform: translateY(-3px); }
  60% { transform: translateY(0); }
  80% { transform: translateY(-1px); }
  100% { transform: translateY(0); }
}

@media (max-width: 768px) {
  .guest-title { font-size: 34px; }
  .dashboard-title { font-size: 28px; }
  .agent-cards-row { flex-direction: column; gap: 10px; max-width: 320px; }
  .agent-card { padding: 18px 14px; flex-direction: row; }
}
</style>
