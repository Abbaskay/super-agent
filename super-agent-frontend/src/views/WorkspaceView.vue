<template>
  <div class="app-shell">
    <header class="app-header">
      <div class="header-left">
        <button class="header-btn" @click="sidebarOpen = !sidebarOpen" title="Toggle history">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="header-divider"></div>
        <button class="header-back" @click="goHome" title="Back to home">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </button>
        <button class="header-logo" @click="goHome" title="Go to dashboard">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </button>
        <button class="header-title-btn" @click="goHome" title="Go to dashboard">Zonrad</button>
        <span v-if="currentRouteLabel" class="route-badge">{{ currentRouteLabel }}</span>
      </div>
      <div class="header-right">
        <button class="header-btn" @click="newChat" title="New conversation">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        </button>
        <div class="user-menu" ref="userMenu">
          <button @click="userMenuOpen = !userMenuOpen" class="user-trigger"><span class="user-avatar">{{ userInitial }}</span></button>
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

    <div class="body">
      <Transition name="sidebar">
        <aside v-if="sidebarOpen" class="sidebar">
          <div class="sidebar-header">
            <span class="sidebar-title">Chat History</span>
            <button class="sidebar-new-btn" @click="newChat">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
              New Chat
            </button>
          </div>
          <div class="sidebar-list">
            <div v-for="chat in chatStore.chats" :key="chat.id" :class="['sidebar-item', { active: chat.id === chatStore.activeId }]" @click="switchChat(chat.id)">
              <div class="sidebar-item-content">
                <span class="sidebar-item-title">{{ chat.title }}</span>
                <span class="sidebar-item-time">{{ formatDate(chat.timestamp) }}</span>
              </div>
              <button class="sidebar-item-del" @click.stop="handleDeleteChat(chat.id)" title="Delete chat">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
            <div v-if="!chatStore.chats.length" class="sidebar-empty">No chats yet</div>
          </div>
        </aside>
      </Transition>

      <div class="chat-area">
        <div class="chat-messages" ref="chatMessages">
          <div v-if="!messages.length" class="chat-empty">
            <div class="chat-empty-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <p class="chat-empty-text">Start a conversation. Ask me anything — I'll route you to the right agent.</p>
          </div>
          <TransitionGroup name="message">
            <div v-for="(msg, i) in messages" :key="i" class="message-group" :class="msg.role">
              <div class="chat-bubble" :class="msg.role">
                <div v-if="msg.role === 'assistant'" class="chat-bubble-avatar">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                </div>
                <div class="chat-bubble-body">
                  <div v-if="msg.role === 'assistant' && msg.routedTo" :class="['routing-badge', routeClass(msg.routedTo)]">Routed to {{ msg.routedTo }}</div>
                  <img v-if="msg.imageUrl" :src="msg.imageUrl" class="chat-inline-image" :alt="'Uploaded image'">
                  <div class="chat-bubble-content" v-html="msg.role === 'assistant' ? renderMarkdown(msg.text) : escapeHtml(msg.text)"></div>
                </div>
                <div v-if="msg.role === 'user'" class="chat-bubble-avatar user">{{ userInitial }}</div>
              </div>
              <div class="chat-timestamp" :class="msg.role">{{ msg.time }}</div>
            </div>
          </TransitionGroup>
          <div v-if="isLoading" class="typing-indicator">
            <div class="chat-bubble-avatar">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
            </div>
            <div class="typing-dots"><span></span><span></span><span></span></div>
          </div>
        </div>

        <div class="input-composer">
          <div class="composer-inner">
            <button class="composer-btn" @click="$refs.fileInput.click()" title="Attach file">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            </button>
            <input ref="fileInput" type="file" accept=".pdf,.docx,.txt,.png,.jpg,.jpeg,.gif,.webp" @change="onFileUpload" hidden>
            <div class="dropdown-wrapper" ref="dropdownWrapper">
              <button class="dropdown-trigger" @click="toggleDropdown">
                <span>{{ selectedAgent }}</span>
                <svg class="dropdown-arrow" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
              </button>
              <Transition name="dropdown">
                <div class="dropdown-menu" v-if="dropdownOpen">
                  <div v-for="opt in agentOptions" :key="opt.value" :class="['dropdown-item', { active: selectedAgent === opt.label }]" @click="selectAgent(opt)">{{ opt.label }}</div>
                </div>
              </Transition>
            </div>
            <div class="composer-input-wrap">
              <div v-if="uploadedImageUrl" class="image-preview-inline">
                <img :src="uploadedImageUrl" class="image-preview-thumb" alt="Uploaded image">
                <span class="image-preview-name">{{ uploadedFilename }}</span>
                <button class="file-badge-remove" @click="clearUpload">✕</button>
              </div>
              <div v-if="uploadedFilename && !uploadedImageUrl" class="file-badge-inline">
                <span class="file-badge-name">{{ uploadedFilename }}</span>
                <button class="file-badge-remove" @click="clearUpload">✕</button>
              </div>
              <input v-model="inputText" type="text" class="composer-input" :placeholder="uploadedImageUrl ? 'Ask about this image...' : uploadedFilename ? 'Ask about this file...' : 'Ask anything...'" autocomplete="off" spellcheck="false" @keydown.enter.prevent="sendMessage" ref="mainInput">
            </div>
            <button class="send-btn" :disabled="(!inputText.trim() && !uploadedImageUrl) || isLoading" @click="sendMessage" aria-label="Send">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
          </div>
          <p class="composer-hint">Zonrad can make mistakes. Consider verifying important information.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { authState } from '../store/auth.js'
import { chatStore, createChat, deleteChat, setActiveChat, getActiveChat, updateActiveChat } from '../store/chats.js'
import { chatAPI } from '../services/api.js'
import * as pdfjs from 'pdfjs-dist'
import mammoth from 'mammoth'
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url'
pdfjs.GlobalWorkerOptions.workerSrc = workerUrl

const ROUTING_KEYWORDS = {
  'AI Docs': ['document', 'write report', 'create proposal', 'draft', 'essay', 'write a', 'write an', 'proposal', 'report on', 'documentation', 'memo', 'letter', 'article', 'blog post', 'white paper', 'manual'],
  'AI Slides': ['slides', 'presentation', 'pitch deck', 'slide deck', 'powerpoint', 'keynote', 'slideshow', 'deck', 'present'],
  'AI Fact Checker': ['verify', 'fact check', 'is this true', 'check this', 'validate', 'fact-check', 'reliable', 'source', 'citation', 'claim', 'truth', 'misinformation', 'fake'],
  'AI Excel': ['excel', 'spreadsheet', 'sheet', 'data analysis', 'table', 'csv', 'xlsx', 'numbers', 'grid', 'worksheet'],
}

export default {
  name: 'WorkspaceView',
  data() {
    return {
      inputText: '',
      messages: [],
      isLoading: false,
      chatHistory: [],
      selectedAgent: 'Auto',
      dropdownOpen: false,
      userMenuOpen: false,
      sidebarOpen: true,
      conversationId: '',
      agentOptions: [
        { label: 'Auto', value: 'auto' },
        { label: 'AI Docs', value: 'docs' },
        { label: 'AI Slides', value: 'slides' },
        { label: 'Fact Checker', value: 'factcheck' },
        { label: 'AI Excel', value: 'excel' },
      ],
      authState,
      chatStore,
      uploadedText: '',
      uploadedFilename: '',
      uploadedImageUrl: '',
    }
  },
  computed: {
    userInitial() { return (authState.user?.name || 'U')[0].toUpperCase() },
    currentRouteLabel() {
      const last = this.messages[this.messages.length - 1]
      return last?.routedTo || null
    },
    isAuthenticated() { return authState.isAuthenticated },
  },
  methods: {
    routeMessage(text) {
      if (this.selectedAgent !== 'Auto') {
        const match = this.agentOptions.find(o => o.label === this.selectedAgent)
        console.log('[Workspace] routeMessage: manual selection', { agent: match?.label })
        return match
      }
      const lower = text.toLowerCase()
      for (const [agent, keywords] of Object.entries(ROUTING_KEYWORDS)) {
        for (const kw of keywords) { if (lower.includes(kw)) { console.log('[Workspace] routeMessage: matched', { agent, keyword: kw }); return { label: agent } } }
      }
      console.log('[Workspace] routeMessage: no match')
      return null
    },
    loadChat(id) {
      console.log('[Workspace] loadChat', { id })
      const chat = chatStore.chats.find(c => c.id === id)
      if (!chat) {
        console.warn('[Workspace] loadChat: chat not found', { id })
        return
      }
      this.messages = chat.messages
      this.chatHistory = chat.chatHistory
      this.conversationId = chat.conversationId || ''
      setActiveChat(id)
      console.log('[Workspace] loadChat: loaded', { messageCount: this.messages.length })
    },
    handleDeleteChat(id) {
      console.log('[Workspace] handleDeleteChat', { id })
      const wasActive = id === chatStore.activeId
      deleteChat(id)
      if (wasActive) {
        const next = getActiveChat()
        if (next) {
          this.loadChat(next.id)
        } else {
          const chat = createChat()
          this.messages = chat.messages
          this.chatHistory = chat.chatHistory
          this.conversationId = ''
        }
      }
    },
    switchChat(id) {
      console.log('[Workspace] switchChat', { id })
      this.saveCurrentChat()
      this.loadChat(id)
    },
    goHome() {
      console.log('[Workspace] goHome')
      this.saveCurrentChat()
      this.$router.push({ name: 'Welcome' })
    },
    newChat() {
      console.log('[Workspace] newChat')
      this.saveCurrentChat()
      const chat = createChat()
      this.messages = chat.messages
      this.chatHistory = chat.chatHistory
      this.conversationId = ''
      this.$nextTick(() => this.$refs.mainInput?.focus())
    },
    saveCurrentChat() {
      const active = getActiveChat()
      if (!active) {
        console.warn('[Workspace] saveCurrentChat: no active chat')
        return
      }
      console.log('[Workspace] saveCurrentChat', { chatId: active.id, messageCount: this.messages.length })
      updateActiveChat({ messages: this.messages, chatHistory: this.chatHistory, conversationId: this.conversationId })
    },
    async sendMessage() {
      const text = this.inputText.trim()
      if ((!text && !this.uploadedImageUrl) || this.isLoading) {
        console.warn('[Workspace] sendMessage: blocked', { empty: !text, hasImage: !!this.uploadedImageUrl, isLoading: this.isLoading })
        return
      }

      console.log('[Workspace] sendMessage', { textPreview: text.slice(0, 80), hasFile: !!this.uploadedText, hasImage: !!this.uploadedImageUrl })

      // Ensure we have an active chat
      if (!getActiveChat()) {
        console.log('[Workspace] sendMessage: creating new chat')
        createChat()
      }

      // Build the displayed message text
      let displayText = text
      let chatHistoryContent = text || '[Sent with image]'
      let msgImageUrl = ''

      if (this.uploadedImageUrl) {
        msgImageUrl = this.uploadedImageUrl
        displayText = text || '[Image]'
        chatHistoryContent = text
          ? `[Attached image: ${this.uploadedFilename}]\n\nUser message: ${text}`
          : `[Sent image: ${this.uploadedFilename}]`
      } else if (this.uploadedText) {
        chatHistoryContent = `[Attached file: ${this.uploadedFilename}]\n\nFile content:\n${this.uploadedText.slice(0, 50000)}\n\nUser message: ${text}`
        displayText = this.uploadedFilename ? `📎 ${this.uploadedFilename}\n\n${text}` : text
      }

      this.inputText = ''
      const time = this.formatTime()
      this.messages.push({ role: 'user', text: displayText, time, imageUrl: msgImageUrl })
      this.chatHistory.push({ role: 'user', content: chatHistoryContent })

      // Auto-title from first user message
      const active = getActiveChat()
      if (active && active.messages.length === 0 && active.title === 'New Chat') {
        const titleText = this.uploadedImageUrl ? `📷 ${this.uploadedFilename}` : this.uploadedFilename ? `📎 ${this.uploadedFilename}` : text
        updateActiveChat({ title: titleText.slice(0, 60) + (titleText.length > 60 ? '...' : '') })
      }

      // Clear uploaded files after building message
      const hadImage = !!this.uploadedImageUrl
      const hadFile = !!this.uploadedText
      this.clearUpload()

      this.isLoading = true
      this.$nextTick(() => this.scrollToBottom())

      try {
        console.log('[Workspace] calling chat API')
        const res = await chatAPI().send(this.chatHistory)
        this.isLoading = false
        const replyTime = this.formatTime()
        const reply = res.data.reply
        const routing = this.routeMessage(text)
        console.log('[Workspace] API response received', { replyLength: reply?.length, routedTo: routing?.label })
        this.messages.push({ role: 'assistant', text: reply, time: replyTime, routedTo: routing?.label || null })
        this.chatHistory.push({ role: 'assistant', content: reply })
      } catch (e) {
        this.isLoading = false
        console.warn('[Workspace] chat API failed, using fallback', { error: e.message })
        const routing = this.routeMessage(text)
        const reply = this.generateFallback(text, routing)
        const replyTime = this.formatTime()
        this.messages.push({ role: 'assistant', text: reply, time: replyTime, routedTo: routing?.label || null })
        this.chatHistory.push({ role: 'assistant', content: reply })
      }

      this.saveCurrentChat()
      this.$nextTick(() => this.scrollToBottom())
    },
    async readFile(file) {
      const ext = file.name.split('.').pop().toLowerCase()
      console.log('[Workspace] readFile', { fileName: file.name, ext, size: file.size })

      const imageExts = ['png', 'jpg', 'jpeg', 'gif', 'webp']
      if (imageExts.includes(ext)) {
        return new Promise((resolve, reject) => {
          const reader = new FileReader()
          reader.onload = () => resolve(reader.result)
          reader.onerror = () => reject(new Error('Failed to read image'))
          reader.readAsDataURL(file)
        })
      }

      if (ext === 'txt') return await file.text()
      if (ext === 'pdf') {
        const buf = await file.arrayBuffer()
        const pdf = await pdfjs.getDocument({ data: buf }).promise
        let text = ''
        for (let i = 1; i <= pdf.numPages; i++) {
          const page = await pdf.getPage(i)
          const content = await page.getTextContent()
          text += content.items.map(c => c.str).join(' ') + '\n'
        }
        console.log('[Workspace] readFile: PDF parsed', { pages: pdf.numPages, textLength: text.length })
        return text
      }
      if (ext === 'docx') {
        const buf = await file.arrayBuffer()
        const result = await mammoth.extractRawText({ arrayBuffer: buf })
        console.log('[Workspace] readFile: DOCX parsed', { textLength: result.value.length })
        return result.value
      }
      console.warn('[Workspace] readFile: unsupported type', { ext })
      throw new Error('Unsupported file type')
    },
    onFileUpload(e) {
      const f = e.target.files?.[0]
      if (!f) {
        console.warn('[Workspace] onFileUpload: no file selected')
        return
      }
      console.log('[Workspace] onFileUpload', { fileName: f.name, fileSize: f.size })
      const ext = f.name.split('.').pop().toLowerCase()
      const imageExts = ['png', 'jpg', 'jpeg', 'gif', 'webp']

      this.uploadedFilename = f.name

      if (imageExts.includes(ext)) {
        this.readFile(f).then(dataUrl => {
          this.uploadedImageUrl = dataUrl
          console.log('[Workspace] image read complete', { dataUrlLength: dataUrl.length })
        }).catch((err) => {
          console.error('[Workspace] image read failed', { error: err.message })
          this.uploadedFilename = ''
          this.uploadedImageUrl = ''
        })
      } else {
        this.readFile(f).then(text => {
          this.uploadedText = text
          console.log('[Workspace] file read complete', { textLength: text.length })
        }).catch((err) => {
          console.error('[Workspace] file read failed', { error: err.message })
          this.uploadedFilename = ''
          this.uploadedText = ''
        })
      }

      e.target.value = ''
    },
    clearUpload() {
      console.log('[Workspace] clearUpload')
      this.uploadedText = ''
      this.uploadedFilename = ''
      this.uploadedImageUrl = ''
    },
    generateFallback(text, routing) {
      const lower = text.toLowerCase().trim()
      if (['hello', 'hi ', 'hey', 'greetings'].some(g => lower.includes(g)))
        return "Hello! I'm **Zonrad**, your AI workspace assistant. I can answer questions, help with research, draft documents, create presentations, and verify facts. Try asking me something!"
      if (['help', 'what can you do', 'capabilities'].some(w => lower.includes(w)))
        return "Here's what I can do:\n\n💬 **General Q&A** — Answer questions and discuss topics\n📄 **AI Docs** — Draft reports, proposals, and documents\n📊 **AI Slides** — Structure presentations and slide decks\n🛡️ **AI Fact Checker** — Verify claims and validate sources\n📋 **AI Excel** — Spreadsheets, data analysis, tables"
      if (/^(what|who|where|when|why|how|explain|define|tell me|describe)\b/.test(lower))
        return "That's a great question! I'd love to give you a detailed answer, but I currently need my AI backend connected. To get full answers:\n\n1. **Set up a DeepSeek API key** in the backend `.env` file\n2. **Ask a more specific question** so I can try to help from my built-in knowledge\n3. Try asking about coding, AI, or world capitals in the meantime!"
      return "I'm here to help! I can answer questions, assist with writing, explain concepts, and more.\n\nWhat would you like help with?"
    },
    formatTime() { return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) },
    formatDate(ts) {
      const d = new Date(ts)
      const now = new Date()
      const diff = now - d
      if (diff < 86400000) return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      if (diff < 172800000) return 'Yesterday'
      return d.toLocaleDateString([], { month: 'short', day: 'numeric' })
    },
    renderMarkdown(text) {
      let html = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      html = html.replace(/```(\w*)\n?([\s\S]*?)```/g, '<pre><code>$2</code></pre>')
      html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
      html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>')
      html = html.replace(/`([^`]+)`/g, '<code>$1</code>')
      html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>')
      html = html.replace(/\r\n/g, '\n')
      let parts = html.split(/\n{2,}/)
      html = parts.map(part => {
        part = part.trim()
        if (!part) return ''
        if (part.startsWith('<pre>')) return part
        part = part.replace(/\n/g, '<br>')
        return `<p>${part}</p>`
      }).join('')
      return html
    },
    escapeHtml(text) { return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') },
    routeClass(agent) {
      if (agent === 'AI Docs') return 'docs'
      if (agent === 'AI Slides') return 'slides'
      if (agent === 'AI Fact Checker') return 'factcheck'
      if (agent === 'AI Excel') return 'excel'
      return 'general'
    },
    scrollToBottom() { const el = this.$refs.chatMessages; if (el) el.scrollTop = el.scrollHeight },
    toggleDropdown() { console.log('[Workspace] toggleDropdown', { wasOpen: this.dropdownOpen }); this.dropdownOpen = !this.dropdownOpen },
    selectAgent(opt) {
      console.log('[Workspace] selectAgent', { agent: opt.label })
      this.selectedAgent = opt.label
      this.dropdownOpen = false
    },
    handleLogout() {
      console.log('[Workspace] handleLogout')
      this.userMenuOpen = false
      this.saveCurrentChat()
      authState.logout()
      this.$router.push({ name: 'Welcome' })
    },
    handleClickOutside(e) {
      if (this.$refs.dropdownWrapper && !this.$refs.dropdownWrapper.contains(e.target)) this.dropdownOpen = false
      if (this.$refs.userMenu && !this.$refs.userMenu.contains(e.target)) this.userMenuOpen = false
    },
    async processPendingUpload() {
      const raw = sessionStorage.getItem('pendingUpload')
      if (!raw) return
      sessionStorage.removeItem('pendingUpload')
      let pending
      try { pending = JSON.parse(raw) } catch { return }
      if (!pending?.dataUrl) return
      console.log('[Workspace] processing pending upload', { filename: pending.filename, isImage: pending.isImage })

      if (pending.isImage) {
        this.uploadedImageUrl = pending.dataUrl
        this.uploadedFilename = pending.filename
        return
      }

      // Document file — decode base64 data URL and parse
      const ext = pending.filename.split('.').pop().toLowerCase()
      const commaIdx = pending.dataUrl.indexOf(',')
      const base64 = commaIdx >= 0 ? pending.dataUrl.slice(commaIdx + 1) : pending.dataUrl
      const binary = atob(base64)

      if (ext === 'txt') {
        this.uploadedText = binary
        this.uploadedFilename = pending.filename
        console.log('[Workspace] pending txt loaded', { length: binary.length })
        return
      }

      // PDF or DOCX — decode to ArrayBuffer then parse
      const bytes = new Uint8Array(binary.length)
      for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i) & 0xff
      const buf = bytes.buffer

      if (ext === 'pdf') {
        try {
          const pdf = await pdfjs.getDocument({ data: buf }).promise
          let text = ''
          for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i)
            const content = await page.getTextContent()
            text += content.items.map(c => c.str).join(' ') + '\n'
          }
          this.uploadedText = text
          this.uploadedFilename = pending.filename
          console.log('[Workspace] pending PDF parsed', { pages: pdf.numPages, textLength: text.length })
        } catch (e) {
          console.error('[Workspace] pending PDF parse failed', { error: e.message })
        }
        return
      }

      if (ext === 'docx') {
        try {
          const result = await mammoth.extractRawText({ arrayBuffer: buf })
          this.uploadedText = result.value
          this.uploadedFilename = pending.filename
          console.log('[Workspace] pending DOCX parsed', { textLength: result.value.length })
        } catch (e) {
          console.error('[Workspace] pending DOCX parse failed', { error: e.message })
        }
      }
    },
  },
  mounted() {
    console.log('[Workspace] mounted', { isAuthenticated: authState.isAuthenticated, userEmail: authState.user?.email })
    document.addEventListener('click', this.handleClickOutside)
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') this.dropdownOpen = false })

    // Restore or create chat
    if (chatStore.activeId && chatStore.chats.find(c => c.id === chatStore.activeId)) {
      console.log('[Workspace] restoring active chat', { id: chatStore.activeId })
      this.loadChat(chatStore.activeId)
    } else if (chatStore.chats.length) {
      console.log('[Workspace] loading first chat', { id: chatStore.chats[0].id })
      this.loadChat(chatStore.chats[0].id)
    } else {
      console.log('[Workspace] creating new chat')
      createChat()
    }

    const prompt = this.$route.query.prompt
    if (prompt) {
      console.log('[Workspace] sending query prompt', { promptPreview: prompt.slice(0, 80) })
      this.inputText = prompt
      // If there's also a pending upload, set it up before sending
      this.$nextTick(async () => {
        await this.processPendingUpload()
        this.sendMessage()
      })
    } else {
      this.$nextTick(async () => {
        await this.processPendingUpload()
        if (this.uploadedImageUrl && !this.inputText.trim()) {
          // Image with no text — send immediately
          this.sendMessage()
        }
        this.$refs.mainInput?.focus()
      })
    }
  },
  beforeUnmount() {
    console.log('[Workspace] beforeUnmount')
    this.saveCurrentChat()
    document.removeEventListener('click', this.handleClickOutside)
  },
}
</script>

<style scoped>
.app-shell { height: 100dvh; display: flex; flex-direction: column; overflow: hidden; }

.app-header {
  height: var(--header-height); flex-shrink: 0; display: flex; align-items: center;
  justify-content: space-between; padding: 0 14px;
  background: rgba(10,10,12,0.82); backdrop-filter: blur(24px) saturate(1.2);
  border-bottom: 1px solid var(--border-subtle); z-index: 100; user-select: none;
}
.header-left { display: flex; align-items: center; gap: 6px; }
.header-back, .header-btn {
  width: 30px; height: 30px; border-radius: var(--radius-sm); border: none;
  background: transparent; color: var(--text-tertiary); cursor: pointer;
  display: flex; align-items: center; justify-content: center; transition: all 0.15s;
}
.header-back:hover, .header-btn:hover { background: var(--bg-glass-hover); color: var(--text-secondary); }
.header-title-btn {
  font-size: 14.5px; font-weight: 600; letter-spacing: -0.01em; color: var(--text-primary);
  background: none; border: none; padding: 0 4px; cursor: pointer;
  font-family: inherit; transition: opacity 0.15s; border-radius: 4px;
}
.header-title-btn:hover { opacity: 0.8; }
.header-logo {
  width: 28px; height: 28px; border-radius: var(--radius-sm);
  background: linear-gradient(135deg, rgba(94,158,255,0.15), rgba(94,92,230,0.15));
  border: 1px solid rgba(94,158,255,0.1);
  display: flex; align-items: center; justify-content: center; color: var(--accent-blue); flex-shrink: 0;
  cursor: pointer; transition: opacity 0.15s;
}
.header-logo:hover { opacity: 0.8; }
.header-title { font-size: 14.5px; font-weight: 600; letter-spacing: -0.01em; color: var(--text-primary); }
.header-divider { width: 1px; height: 18px; background: var(--border-subtle); flex-shrink: 0; }
.route-badge {
  font-size: 10px; font-weight: 600; padding: 3px 10px; border-radius: var(--radius-full);
  background: rgba(94,158,255,0.08); color: rgba(94,158,255,0.8);
  border: 1px solid rgba(94,158,255,0.08); text-transform: uppercase; letter-spacing: 0.03em;
}
.header-right { display: flex; align-items: center; gap: 6px; }

/* ── BODY ── */
.body { flex: 1; display: flex; min-height: 0; overflow: hidden; }

/* ── SIDEBAR ── */
.sidebar {
  width: 320px; flex-shrink: 0; display: flex; flex-direction: column;
  background: var(--bg-secondary); border-right: 1px solid var(--border-subtle);
  overflow: hidden;
}
.sidebar-enter-active, .sidebar-leave-active { transition: width 0.2s, opacity 0.2s; overflow: hidden; }
.sidebar-enter-from, .sidebar-leave-to { width: 0; opacity: 0; }

.sidebar-header {
  padding: 12px; display: flex; flex-direction: column; gap: 8px;
  border-bottom: 1px solid var(--border-subtle);
}
.sidebar-title { font-size: 13px; font-weight: 600; color: var(--text-secondary); }
.sidebar-new-btn {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  padding: 7px; border-radius: var(--radius-sm); border: 1px dashed var(--border-default);
  background: transparent; color: var(--text-tertiary); font-size: 12.5px;
  cursor: pointer; transition: all 0.15s; font-family: inherit;
}
.sidebar-new-btn:hover { border-color: var(--accent-blue); color: var(--accent-blue); background: rgba(94,158,255,0.04); }

.sidebar-list { flex: 1; overflow-y: auto; padding: 6px; }
.sidebar-item {
  display: flex; align-items: center; gap: 4px; padding: 8px 10px;
  border-radius: var(--radius-sm); cursor: pointer; transition: all 0.1s;
  margin-bottom: 2px; position: relative;
}
.sidebar-item:hover { background: var(--bg-glass); }
.sidebar-item.active { background: rgba(94,158,255,0.08); }
.sidebar-item-content { flex: 1; min-width: 0; }
.sidebar-item-title { display: block; font-size: 13px; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sidebar-item.active .sidebar-item-title { color: var(--text-primary); }
.sidebar-item-time { display: block; font-size: 11px; color: var(--text-tertiary); margin-top: 2px; }
.sidebar-item-del {
  width: 24px; height: 24px; border-radius: 4px; border: none;
  background: transparent; color: var(--text-tertiary); cursor: pointer;
  display: none; align-items: center; justify-content: center; flex-shrink: 0;
  transition: all 0.1s;
}
.sidebar-item:hover .sidebar-item-del { display: flex; }
.sidebar-item-del:hover { color: var(--accent-red); background: rgba(255,69,58,0.06); }
.sidebar-empty { padding: 20px; text-align: center; font-size: 13px; color: var(--text-tertiary); }

/* ── CHAT AREA ── */
.chat-area { flex: 1; display: flex; flex-direction: column; min-width: 0; }

.chat-messages {
  flex: 1; overflow-y: auto; padding: 24px 0 16px; width: 100%; max-width: 700px;
  margin: 0 auto; display: flex; flex-direction: column; scroll-behavior: smooth;
}
.chat-empty {
  flex: 1; display: flex; flex-direction: column; align-items: center;
  justify-content: center; text-align: center; padding: 40px 20px;
}
.chat-empty-icon {
  width: 48px; height: 48px; border-radius: var(--radius-lg);
  background: linear-gradient(135deg, rgba(94,158,255,0.1), rgba(94,92,230,0.08));
  border: 1px solid rgba(94,158,255,0.08);
  display: flex; align-items: center; justify-content: center; color: var(--accent-blue); margin-bottom: 16px;
}
.chat-empty-text { font-size: 14px; color: var(--text-tertiary); max-width: 360px; line-height: 1.6; }

.message-group { animation: messageIn 0.35s cubic-bezier(0.4,0,0.2,1); margin-bottom: 22px; }
.message-group:last-child { margin-bottom: 0; }
.chat-bubble { display: flex; gap: 12px; padding: 0 20px; }
.chat-bubble.user { flex-direction: row-reverse; }
.chat-bubble-avatar {
  width: 32px; height: 32px; border-radius: 10px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; margin-top: 4px;
}
.chat-bubble.user .chat-bubble-avatar {
  background: var(--user-gradient); color: white; border: none; font-size: 13px; font-weight: 600;
  box-shadow: 0 2px 8px rgba(94,92,230,0.2);
}
.chat-bubble.assistant .chat-bubble-avatar {
  background: linear-gradient(135deg, rgba(94,158,255,0.12), rgba(94,92,230,0.08));
  border: 1px solid rgba(255,255,255,0.04); color: rgba(255,255,255,0.5);
}
.chat-bubble-body { max-width: 78%; min-width: 0; }
.chat-bubble-content {
  padding: 16px 22px; font-size: 15px; font-weight: 400; line-height: 1.75;
  letter-spacing: -0.01em; word-wrap: break-word;
}
.chat-bubble.user .chat-bubble-content {
  background: var(--user-gradient); color: rgba(255,255,255,0.95);
  border-radius: 22px 22px 4px 22px; box-shadow: 0 2px 12px rgba(94,92,230,0.15);
}
.chat-bubble.assistant .chat-bubble-content {
  background: rgba(24,24,28,0.72); backdrop-filter: blur(18px);
  border: 1px solid rgba(255,255,255,0.04);
  border-radius: 22px 22px 22px 4px; box-shadow: 0 1px 4px rgba(0,0,0,0.15), 0 4px 16px rgba(0,0,0,0.1);
  color: rgba(255,255,255,0.88);
}
.chat-bubble-content p { margin-bottom: 14px; line-height: 1.75; }
.chat-bubble-content p:last-child { margin-bottom: 0; }
.chat-bubble-content p:empty { display: none; }
.chat-bubble.assistant .chat-bubble-content strong { color: rgba(255,255,255,0.95); font-weight: 600; }
.chat-bubble-content code {
  background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 4px;
  font-size: 13.5px; font-family: 'SF Mono', 'Fira Code', monospace; color: rgba(94,158,255,0.9);
}
.chat-bubble-content pre {
  background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.04);
  border-radius: 12px; padding: 14px 16px; margin: 12px 0; overflow-x: auto;
}
.chat-bubble-content pre code { background: transparent; padding: 0; font-size: 13px; color: rgba(255,255,255,0.8); }
.chat-bubble-content a { color: rgba(94,158,255,0.9); text-decoration: none; }
.chat-bubble-content a:hover { text-decoration: underline; }
.chat-inline-image {
  max-width: 100%; max-height: 320px; border-radius: var(--radius-md);
  margin-bottom: 10px; display: block; object-fit: contain;
  background: rgba(0,0,0,0.15); border: 1px solid var(--border-subtle);
}
.chat-bubble-content ul, .chat-bubble-content ol { padding-left: 22px; margin: 8px 0; }
.chat-bubble-content li { margin-bottom: 4px; line-height: 1.65; }

.routing-badge {
  display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px;
  border-radius: var(--radius-full); font-size: 10px; font-weight: 600;
  letter-spacing: 0.03em; margin-bottom: 10px; text-transform: uppercase;
}
.routing-badge.docs { background: rgba(94,158,255,0.08); color: rgba(94,158,255,0.8); border: 1px solid rgba(94,158,255,0.08); }
.routing-badge.slides { background: rgba(245,166,35,0.08); color: rgba(245,166,35,0.8); border: 1px solid rgba(245,166,35,0.08); }
.routing-badge.factcheck { background: rgba(52,199,89,0.08); color: rgba(52,199,89,0.8); border: 1px solid rgba(52,199,89,0.08); }
.routing-badge.excel { background: rgba(167,139,250,0.08); color: rgba(167,139,250,0.8); border: 1px solid rgba(167,139,250,0.08); }

.chat-timestamp { font-size: 11px; color: rgba(255,255,255,0.2); padding: 5px 20px 0; }
.message-group.user .chat-timestamp { text-align: right; }

.typing-indicator {
  display: flex; gap: 12px; padding: 0 20px; margin-bottom: 22px; animation: messageIn 0.25s ease;
}
.typing-indicator .chat-bubble-avatar {
  background: linear-gradient(135deg, rgba(94,158,255,0.12), rgba(94,92,230,0.08));
  border: 1px solid rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); margin-top: 4px;
}
.typing-dots {
  display: flex; gap: 5px; align-items: center; padding: 16px 22px;
  background: rgba(24,24,28,0.72); backdrop-filter: blur(18px);
  border: 1px solid rgba(255,255,255,0.04);
  border-radius: 22px 22px 22px 4px;
}
.typing-dots span {
  width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,0.25);
  animation: typingDot 1.3s ease-in-out infinite;
}
.typing-dots span:nth-child(2) { animation-delay: 0.15s; }
.typing-dots span:nth-child(3) { animation-delay: 0.3s; }

.input-composer {
  flex-shrink: 0; padding: 8px 16px calc(8px + env(safe-area-inset-bottom, 4px));
  position: relative; z-index: 50;
  background: linear-gradient(to top, var(--bg-primary) 70%, transparent);
}
.composer-inner {
  display: flex; align-items: center; gap: 4px;
  background: var(--bg-secondary); border: 1px solid var(--border-default);
  border-radius: var(--radius-xl); padding: 5px 5px 5px 6px;
  max-width: 720px; margin: 0 auto;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-shadow: 0 4px 24px rgba(0,0,0,0.3);
}
.composer-inner:focus-within { border-color: rgba(94,158,255,0.25); box-shadow: 0 4px 32px rgba(0,0,0,0.35), 0 0 0 1px rgba(94,158,255,0.06); }
.composer-btn {
  width: 34px; height: 34px; border-radius: var(--radius-full); border: none;
  background: transparent; color: var(--text-tertiary); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.15s; flex-shrink: 0;
}
.composer-btn:hover { background: var(--bg-glass-hover); color: var(--text-secondary); }
.composer-btn:active { transform: scale(0.9); }
.composer-input-wrap { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.composer-input {
  background: transparent; border: none; outline: none;
  color: var(--text-primary); font-size: 16px; font-weight: 400; padding: 10px 0;
  font-family: inherit; line-height: 1.4; width: 100%;
}
.composer-input::placeholder { color: var(--text-tertiary); }
.file-badge-inline {
  display: flex; align-items: center; gap: 6px; padding: 3px 0 0;
  font-size: 12px; color: var(--accent-blue);
}
.file-badge-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-badge-remove {
  background: none; border: none; color: var(--text-tertiary); cursor: pointer;
  font-size: 12px; padding: 0 2px; line-height: 1;
}
.file-badge-remove:hover { color: var(--accent-red); }
.image-preview-inline {
  display: flex; align-items: center; gap: 8px; padding: 4px 0;
}
.image-preview-thumb {
  width: 36px; height: 36px; border-radius: 6px; object-fit: cover;
  border: 1px solid var(--border-subtle); flex-shrink: 0;
}
.image-preview-name {
  font-size: 12px; color: var(--accent-blue); white-space: nowrap;
  overflow: hidden; text-overflow: ellipsis; flex: 1; min-width: 0;
}

.dropdown-wrapper { position: relative; flex-shrink: 0; }
.dropdown-trigger {
  display: flex; align-items: center; gap: 3px; padding: 6px 10px;
  border-radius: var(--radius-full); background: transparent; border: none;
  color: var(--text-tertiary); font-size: 13px; font-weight: 500; cursor: pointer;
  transition: all 0.2s; font-family: inherit;
}
.dropdown-trigger:hover { color: var(--text-secondary); background: var(--bg-glass); }
.dropdown-arrow { transition: transform 0.2s; }
.dropdown-wrapper.open .dropdown-arrow { transform: rotate(180deg); }
.dropdown-menu {
  position: absolute; top: calc(100% + 5px); right: 0; min-width: 140px;
  background: rgba(16,16,18,0.98); backdrop-filter: blur(24px);
  border: 1px solid var(--border-subtle); border-radius: var(--radius-md);
  padding: 4px; z-index: 60; box-shadow: 0 16px 48px rgba(0,0,0,0.6);
}
.dropdown-enter-active, .dropdown-leave-active { transition: all 0.15s; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-5px) scale(0.96); }
.dropdown-item {
  padding: 7px 12px; border-radius: 5px; color: var(--text-secondary);
  font-size: 13px; cursor: pointer; transition: all 0.1s;
}
.dropdown-item:hover { background: var(--bg-glass); color: var(--text-primary); }
.dropdown-item.active { color: var(--text-primary); background: rgba(94,158,255,0.08); }

.send-btn {
  width: 40px; height: 40px; border-radius: var(--radius-full); border: none;
  background: var(--accent-blue); color: white; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s; box-shadow: 0 2px 10px rgba(94,158,255,0.25); flex-shrink: 0;
}
.send-btn:hover { background: #4a8be7; transform: scale(1.04); box-shadow: 0 4px 16px rgba(94,158,255,0.3); }
.send-btn:active { transform: scale(0.92); }
.send-btn:disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; }

.composer-hint { text-align: center; font-size: 11px; color: var(--text-tertiary); opacity: 0.4; margin-top: 8px; padding: 0 16px; }

.user-menu { position: relative; }
.user-trigger {
  width: 30px; height: 30px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.06);
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, var(--accent-indigo), var(--accent-blue));
  color: white; font-size: 12px; font-weight: 600;
}
.user-dropdown {
  position: absolute; top: calc(100% + 8px); right: 0; min-width: 200px;
  background: rgba(16,16,18,0.98); backdrop-filter: blur(24px);
  border: 1px solid var(--border-subtle); border-radius: var(--radius-md);
  padding: 8px; z-index: 60; box-shadow: 0 16px 48px rgba(0,0,0,0.6);
}
.ud-user { padding: 4px 8px 8px; }
.ud-name { display: block; font-size: 14px; font-weight: 600; color: var(--text-primary); }
.ud-email { display: block; font-size: 12px; color: var(--text-tertiary); margin-top: 2px; }
.ud-divider { height: 1px; background: var(--border-subtle); margin: 4px 0; }
.ud-item {
  display: block; width: 100%; padding: 7px 8px; border-radius: 5px;
  font-size: 13px; color: var(--text-secondary); text-decoration: none;
  cursor: pointer; transition: all 0.1s; border: none; background: none; text-align: left; font-family: inherit;
}
.ud-item:hover { background: var(--bg-glass); color: var(--text-primary); }
.ud-logout:hover { color: var(--accent-red); }

.menu-enter-active, .menu-leave-active { transition: all 0.15s; }
.menu-enter-from, .menu-leave-to { opacity: 0; transform: translateY(-5px) scale(0.96); }
.message-enter-active, .message-leave-active { transition: all 0.3s ease; }
.message-enter-from { opacity: 0; transform: translateY(12px); }

@keyframes messageIn { from { opacity: 0; transform: translateY(8px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
@keyframes typingDot { 0%,60%,100% { opacity: 0.15; transform: scale(0.8); } 30% { opacity: 1; transform: scale(1); } }

@media (max-width: 768px) {
  .sidebar { width: 220px; }
  .chat-messages { padding: 16px 0 12px; }
  .chat-bubble { padding: 0 12px; }
  .message-group { margin-bottom: 18px; }
  .chat-bubble-body { max-width: 88%; }
  .chat-bubble-content { font-size: 14px; padding: 14px 18px; }
  .chat-timestamp { padding: 4px 12px 0; }
  .composer-inner { padding: 4px 4px 4px 10px; border-radius: var(--radius-lg); }
  .composer-input { font-size: 15px; padding: 10px 0; }
  .send-btn { width: 36px; height: 36px; }
  .input-composer { padding: 6px 12px calc(6px + env(safe-area-inset-bottom, 4px)); }
}
</style>
