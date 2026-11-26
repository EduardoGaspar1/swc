<script setup>
import { onBeforeUnmount, useId, watch } from 'vue'
import { AlertTriangle, X } from '@lucide/vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: 'Confirmar' },
  message: { type: String, default: '' },
  confirmLabel: { type: String, default: 'Confirmar' },
  cancelLabel: { type: String, default: 'Cancelar' },
  loadingLabel: { type: String, default: 'Procesando…' },
  /** 'danger' | 'default' */
  variant: { type: String, default: 'danger' },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['confirm', 'cancel', 'update:open'])

const titleId = useId()
const descId = useId()

function close() {
  if (props.loading) return
  emit('update:open', false)
  emit('cancel')
}

function confirm() {
  if (props.loading) return
  emit('confirm')
}

function onKeydown(event) {
  if (!props.open) return
  if (event.key === 'Escape') {
    event.preventDefault()
    close()
  }
}

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      document.addEventListener('keydown', onKeydown)
      document.body.style.overflow = 'hidden'
    } else {
      document.removeEventListener('keydown', onKeydown)
      document.body.style.overflow = ''
    }
  },
)

onBeforeUnmount(() => {
  document.removeEventListener('keydown', onKeydown)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <Transition name="confirm-fade">
      <div
        v-if="open"
        class="confirm-backdrop"
        @click.self="close"
      >
        <div
          class="confirm-dialog"
          role="alertdialog"
          aria-modal="true"
          :aria-labelledby="titleId"
          :aria-describedby="message ? descId : undefined"
        >
          <div class="confirm-header">
            <div class="confirm-heading">
              <span
                class="confirm-icon"
                :class="variant"
                aria-hidden="true"
              >
                <AlertTriangle :size="18" :stroke-width="2" />
              </span>
              <h2 :id="titleId" class="confirm-title">{{ title }}</h2>
            </div>
            <button
              type="button"
              class="confirm-close"
              title="Cerrar"
              aria-label="Cerrar"
              :disabled="loading"
              @click="close"
            >
              <X :size="18" :stroke-width="1.75" />
            </button>
          </div>

          <p v-if="message" :id="descId" class="confirm-message">
            {{ message }}
          </p>

          <div class="confirm-actions">
            <button
              type="button"
              class="btn ghost"
              :disabled="loading"
              @click="close"
            >
              {{ cancelLabel }}
            </button>
            <button
              type="button"
              class="btn"
              :class="variant === 'danger' ? 'danger' : 'primary'"
              :disabled="loading"
              @click="confirm"
            >
              {{ loading ? loadingLabel : confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.confirm-backdrop {
  position: fixed;
  inset: 0;
  z-index: 90;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  background: rgba(0, 0, 0, 0.55);
}

.confirm-dialog {
  width: 100%;
  max-width: 400px;
  background: var(--swc-bg-elevated);
  border: 1px solid var(--swc-border);
  border-radius: var(--swc-radius);
  box-shadow: var(--swc-shadow);
  padding: 1.2rem 1.25rem 1.3rem;
}

.confirm-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.75rem;
}

.confirm-heading {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  min-width: 0;
}

.confirm-icon {
  flex-shrink: 0;
  width: 2.25rem;
  height: 2.25rem;
  border-radius: var(--swc-radius-sm);
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.confirm-icon.danger {
  color: var(--swc-danger);
  background: var(--swc-danger-bg);
}

.confirm-icon.default {
  color: var(--swc-info);
  background: var(--swc-info-bg);
}

.confirm-title {
  color: var(--swc-heading);
  font-size: 1.05rem;
  font-weight: 600;
  line-height: 1.3;
}

.confirm-close {
  appearance: none;
  border: 1px solid transparent;
  background: transparent;
  color: var(--swc-text-muted);
  width: 2rem;
  height: 2rem;
  flex-shrink: 0;
  border-radius: var(--swc-radius-sm);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.confirm-close:hover:not(:disabled) {
  color: var(--swc-heading);
  background: var(--swc-bg-soft);
  border-color: var(--swc-border);
}

.confirm-close:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.confirm-message {
  margin: 0.85rem 0 0;
  color: var(--swc-text-muted);
  font-size: 0.92rem;
  line-height: 1.45;
}

.confirm-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.55rem;
  margin-top: 1.25rem;
}

.btn {
  appearance: none;
  border: 0;
  border-radius: var(--swc-radius-sm);
  padding: 0.65rem 0.95rem;
  font: inherit;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.btn.primary {
  background: var(--swc-info);
  color: #0b1520;
}

.btn.danger {
  background: var(--swc-danger);
  color: #fff;
}

.btn.ghost {
  background: transparent;
  border: 1px solid var(--swc-border);
  color: var(--swc-text);
}

.btn:disabled {
  opacity: 0.65;
  cursor: wait;
}

.confirm-fade-enter-active,
.confirm-fade-leave-active {
  transition: opacity 0.18s ease;
}

.confirm-fade-enter-active .confirm-dialog,
.confirm-fade-leave-active .confirm-dialog {
  transition: transform 0.18s ease, opacity 0.18s ease;
}

.confirm-fade-enter-from,
.confirm-fade-leave-to {
  opacity: 0;
}

.confirm-fade-enter-from .confirm-dialog,
.confirm-fade-leave-to .confirm-dialog {
  opacity: 0;
  transform: translateY(6px) scale(0.98);
}
</style>
