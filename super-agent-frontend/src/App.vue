<template>
  <div class="app">
    <router-view />
  </div>
</template>

<script>
export default {
  name: 'App',
  mounted() {
    console.log('[App] mounted')
  },
}
</script>

<style>
:root {
  /* Legacy aliases — kept so sub-views that reference them still work */
  --bg-primary: #080808;
  --bg-secondary: #111111;
  --bg-glass: rgba(255, 255, 255, 0.03);
  --bg-glass-hover: rgba(255, 255, 255, 0.06);
  --border-subtle: rgba(255, 255, 255, 0.06);
  --border-default: rgba(255, 255, 255, 0.08);
  --border-hover: rgba(255, 255, 255, 0.12);
  --text-primary: #f0ede8;
  --text-secondary: #a8a29e;
  --text-tertiary: #6b6561;
  --accent-blue: #0095ff;
  --accent-indigo: #0066cc;
  --accent-amber: #f5a623;
  --accent-emerald: #34c759;
  --accent-red: #ff453a;
  --user-gradient: linear-gradient(135deg, #0066cc, #0095ff);
  --radius-xs: 6px;
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 20px;
  --radius-full: 999px;
  --font: 'DM Sans', sans-serif;
  --header-height: 54px;

  --zp-bg: #070b15;
  --zp-bg-elevated: #111111;
  --zp-bg-card: #141414;
  --zp-bg-glass: rgba(255, 255, 255, 0.03);
  --zp-bg-glass-hover: rgba(255, 255, 255, 0.06);
  --zp-border: rgba(255, 255, 255, 0.06);
  --zp-border-hover: rgba(255, 255, 255, 0.12);
  --zp-accent: #0095ff;
  --zp-accent-light: #40bfff;
  --zp-accent-dark: #0066cc;
  --zp-accent-glow: rgba(0, 149, 255, 0.12);
  --zp-accent-glow-strong: rgba(0, 149, 255, 0.25);
  --zp-text: #f0ede8;
  --zp-text-secondary: #a8a29e;
  --zp-text-tertiary: #6b6561;
  --zp-success: #22c55e;
  --zp-error: #ef4444;
  --zp-header-h: 54px;
  --zp-radius-sm: 6px;
  --zp-radius-md: 10px;
  --zp-radius-lg: 14px;
  --zp-radius-xl: 18px;
  --zp-radius-full: 999px;
  --zp-font-display: 'Syne', sans-serif;
  --zp-font-body: 'DM Sans', sans-serif;
}

*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
html, body { height: 100%; overflow: hidden; }
body {
  font-family: var(--zp-font-body);
  background: var(--zp-bg);
  color: var(--zp-text);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.05); border-radius: 2px; }

.app {
  height: 100dvh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: var(--zp-bg);
}

.page-enter-active, .page-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.page-enter-from { opacity: 0; transform: translateY(6px); }
.page-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
