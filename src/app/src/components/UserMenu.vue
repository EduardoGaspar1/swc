<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/useUserStore'

const auth = useUserStore()
const router = useRouter()

const open = ref(false)
const root = ref(null)

function toggle() {
  open.value = !open.value
}

function close() {
  open.value = false
}

async function goSettings() {
  close()
  await router.push('/settings')
}

async function onLogout() {
  close()
  await auth.logout()
  await router.push('/login')
}

function onDocumentClick(event) {
  if (!root.value?.contains(event.target)) {
    close()
  }
}

function onEscape(event) {
  if (event.key === 'Escape') close()
}

onMounted(() => {
  document.addEventListener('click', onDocumentClick)
  document.addEventListener('keydown', onEscape)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick)
  document.removeEventListener('keydown', onEscape)
})
</script>

<template>
  <div ref="root" class="user-menu">
    <button
      type="button"
      class="user-trigger"
      :aria-expanded="open"
      aria-haspopup="menu"
      @click="toggle"
    >
      <span class="user-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75">
          <circle cx="12" cy="8" r="3.5" />
          <path d="M5 19.5c1.5-3 4-4.5 7-4.5s5.5 1.5 7 4.5" />
        </svg>
      </span>
      <span class="user-name">{{ auth.user?.name || 'Usuario' }}</span>
      <span class="chevron" aria-hidden="true" :class="{ open }">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M6 9l6 6 6-6" />
        </svg>
      </span>
    </button>

    <div v-if="open" class="dropdown" role="menu">
      <button type="button" class="dropdown-item" role="menuitem" @click="goSettings">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
          <circle cx="12" cy="12" r="3" />
          <path
            d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9c.3.6.9 1 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"
          />
        </svg>
        Configuraciones
      </button>
      <button type="button" class="dropdown-item danger" role="menuitem" :disabled="auth.loading" @click="onLogout">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
          <path d="M16 17l5-5-5-5" />
          <path d="M21 12H9" />
        </svg>
        Cerrar sesión
      </button>
    </div>
  </div>
</template>

<style scoped>
.user-menu {
  position: relative;
}

.user-trigger {
  appearance: none;
  border: 1px solid transparent;
  background: transparent;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--swc-text-muted);
  font: inherit;
  font-size: 0.95rem;
  padding: 0.35rem 0.55rem;
  border-radius: var(--swc-radius-sm);
  cursor: pointer;
  transition:
    background 0.15s ease,
    border-color 0.15s ease,
    color 0.15s ease;
}

.user-trigger:hover,
.user-trigger[aria-expanded='true'] {
  background: var(--swc-bg-elevated);
  border-color: var(--swc-border);
  color: var(--swc-text);
}

.user-icon {
  display: inline-flex;
  opacity: 0.95;
}

.user-name {
  max-width: 12rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.chevron {
  display: inline-flex;
  transition: transform 0.15s ease;
}

.chevron.open {
  transform: rotate(180deg);
}

.dropdown {
  position: absolute;
  top: calc(100% + 0.4rem);
  right: 0;
  min-width: 190px;
  background: var(--swc-bg-elevated);
  border: 1px solid var(--swc-border);
  border-radius: var(--swc-radius-sm);
  box-shadow: var(--swc-shadow);
  padding: 0.35rem;
  z-index: 40;
}

.dropdown-item {
  appearance: none;
  width: 100%;
  border: 0;
  background: transparent;
  color: var(--swc-text);
  display: flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.65rem 0.7rem;
  border-radius: 6px;
  font: inherit;
  font-size: 0.9rem;
  cursor: pointer;
  text-align: left;
}

.dropdown-item:hover:not(:disabled) {
  background: var(--swc-bg-soft);
}

.dropdown-item.danger {
  color: var(--swc-danger);
}

.dropdown-item:disabled {
  opacity: 0.6;
  cursor: wait;
}
</style>
