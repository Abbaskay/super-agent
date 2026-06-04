import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index.js'

console.log('[Main] Zonrad frontend initializing')

// Global error handler — catches uncaught errors and rejections
window.onerror = (msg, url, line, col, err) => {
  console.error('[Global] Uncaught error', {
    message: msg,
    url,
    line,
    col,
    stack: err?.stack,
  })
}
window.addEventListener('unhandledrejection', (event) => {
  console.error('[Global] Unhandled promise rejection', {
    reason: event.reason?.message || event.reason,
    stack: event.reason?.stack,
  })
})

const app = createApp(App)
app.use(router)
app.mount('#app')
console.log('[Main] Zonrad frontend mounted')
