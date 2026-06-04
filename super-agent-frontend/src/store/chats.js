import { reactive } from 'vue'

function loadChats() {
  try {
    const saved = localStorage.getItem('superagent_chats')
    return saved ? JSON.parse(saved) : []
  } catch { return [] }
}

function saveChats() {
  try {
    localStorage.setItem('superagent_chats', JSON.stringify(store.chats))
  } catch {}
}

const store = reactive({
  chats: loadChats(),
  activeId: null,
})

function genId() {
  return 'chat_' + Date.now() + '_' + Math.random().toString(36).slice(2, 7)
}

export const chatStore = store

export function createChat(prompt = '') {
  const id = genId()
  console.log('[Chats] createChat', { id, promptPreview: prompt?.slice(0, 60) || '(empty)' })
  const chat = {
    id,
    title: prompt ? prompt.slice(0, 60) + (prompt.length > 60 ? '...' : '') : 'New Chat',
    messages: [],
    chatHistory: [],
    conversationId: '',
    timestamp: Date.now(),
  }
  store.chats.unshift(chat)
  store.activeId = chat.id
  saveChats()
  return chat
}

export function deleteChat(id) {
  console.log('[Chats] deleteChat', { id })
  store.chats = store.chats.filter(c => c.id !== id)
  if (store.activeId === id) {
    store.activeId = store.chats[0]?.id || null
    console.log('[Chats] activeId updated to', store.activeId)
  }
  saveChats()
}

export function renameChat(id, title) {
  console.log('[Chats] renameChat', { id, title: title?.slice(0, 60) })
  const chat = store.chats.find(c => c.id === id)
  if (chat) {
    chat.title = title.slice(0, 60) + (title.length > 60 ? '...' : '')
    saveChats()
  } else {
    console.warn('[Chats] renameChat: chat not found', { id })
  }
}

export function setActiveChat(id) {
  console.log('[Chats] setActiveChat', { id })
  store.activeId = id
}

export function getActiveChat() {
  const chat = store.chats.find(c => c.id === store.activeId) || null
  console.log('[Chats] getActiveChat', { activeId: store.activeId, found: !!chat })
  return chat
}

export function updateActiveChat(data) {
  const chat = getActiveChat()
  if (!chat) {
    console.warn('[Chats] updateActiveChat: no active chat', { data })
    return
  }
  console.log('[Chats] updateActiveChat', { keys: Object.keys(data) })
  Object.assign(chat, data)
  saveChats()
}
