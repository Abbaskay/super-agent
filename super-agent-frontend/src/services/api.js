import axios from 'axios'
import { authState } from '../store/auth.js'
import { createApiLogger, logger } from '@shared/logger.js'

const BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

const api = axios.create({
  baseURL: BASE_URL,
  headers: { 'Content-Type': 'application/json' },
  withCredentials: true,
})

createApiLogger('Zonrad', api)

api.interceptors.request.use((config) => {
  if (authState.token) {
    config.headers.Authorization = `Bearer ${authState.token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const url = error.config?.url || ''
      if (!url.includes('/chat') && !url.includes('/validate-token')) {
        logger.warn('Zonrad', '401 detected, logging out')
        authState.logout()
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export function chatAPI() {
  return {
    send(messages) {
      console.log('[ChatAPI] send', { messageCount: messages.length, lastPreview: messages[messages.length - 1]?.content?.slice(0, 80) })
      return api.post('/api/chat', { messages })
    },
  }
}

export function difyChat() {
  const DIFY_URL = import.meta.env.VITE_DIFY_API_URL || 'http://localhost/v1/workflows/run'
  const DIFY_KEY = import.meta.env.VITE_DIFY_APP_KEY || ''

  return {
    async send(messages, conversationId = '') {
      console.log('[DifyChat] send', { messageCount: messages.length, conversationId })
      const lastMsg = messages[messages.length - 1]?.content || ''
      const res = await axios.post(DIFY_URL, {
        inputs: { prompt: lastMsg, messages: messages.slice(0, -1) },
        response_mode: 'blocking',
        user: 'zonrad',
        conversation_id: conversationId,
      }, {
        headers: DIFY_KEY ? { Authorization: `Bearer ${DIFY_KEY}` } : {},
      })
      const data = res.data.data || res.data
      let reply = data.outputs?.text || data.outputs?.reply || data.outputs?.output || data.answer || ''
      reply = reply.replace(/<think>[\s\S]*?<\/think>/g, '').trim()
      console.log('[DifyChat] reply received', { replyLength: reply.length, conversationId: data.conversation_id })
      return {
        reply,
        conversation_id: data.conversation_id || conversationId,
      }
    },
  }
}

export function authAPI() {
  return {
    login(email, password) {
      console.log('[AuthAPI] login', { email })
      return api.post('/api/auth/login', { email, password })
    },
    register(name, email, password) {
      console.log('[AuthAPI] register', { name, email })
      return api.post('/api/auth/register', { name, email, password })
    },
    me() {
      console.log('[AuthAPI] me')
      return api.get('/api/auth/me')
    },
  }
}

export default api
