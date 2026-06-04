import { createRouter, createWebHistory } from 'vue-router'
import { authState } from '../store/auth.js'

const routes = [
  {
    path: '/',
    name: 'Welcome',
    component: () => import('../views/WelcomeView.vue'),
  },
  {
    path: '/workspace',
    name: 'Workspace',
    component: () => import('../views/WorkspaceView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
  },
  {
    path: '/signup',
    name: 'Signup',
    component: () => import('../views/SignupView.vue'),
  },
  {
    path: '/pricing',
    name: 'Pricing',
    component: () => import('../views/PricingView.vue'),
  },
  {
    path: '/auth/callback',
    name: 'AuthCallback',
    component: () => import('../views/AuthCallbackView.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

const publicRoutes = ['Welcome', 'Login', 'Signup', 'Pricing', 'AuthCallback']

router.beforeEach((to, from, next) => {
  console.log('[Router] navigating', { from: from.name || '(none)', to: to.name, requiresAuth: !!to.meta.requiresAuth, isAuthenticated: authState.isAuthenticated })
  if (to.meta.requiresAuth && !authState.isAuthenticated) {
    console.warn('[Router] redirecting to login (auth required)')
    next({ name: 'Login', query: { redirect: to.fullPath } })
  } else if (to.name === 'Login' && authState.isAuthenticated) {
    console.log('[Router] redirecting to welcome (already logged in)')
    next({ name: 'Welcome' })
  } else if (to.name === 'Signup' && authState.isAuthenticated) {
    console.log('[Router] redirecting to welcome (already logged in)')
    next({ name: 'Welcome' })
  } else {
    next()
  }
})

export default router
