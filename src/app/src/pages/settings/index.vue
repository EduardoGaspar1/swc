<script setup>
import { reactive, ref, watch } from 'vue'
import { useFlashMessage } from '@/composables/useFlashMessage'
import { useUserStore } from '@/stores/useUserStore'

const auth = useUserStore()
const { message: success, flash: flashSuccess, clear: clearSuccess } = useFlashMessage()

const form = reactive({
  name: auth.user?.name || '',
  password: '',
  password_confirmation: '',
})

const error = ref('')
const fieldErrors = ref({})

watch(
  () => auth.user,
  (user) => {
    if (user) form.name = user.name
  },
  { immediate: true },
)

function extractError(err) {
  const data = err.response?.data
  if (data?.errors) {
    fieldErrors.value = Object.fromEntries(
      Object.entries(data.errors).map(([key, messages]) => [key, messages?.[0] ?? '']),
    )
    return ''
  }
  return data?.message || 'No se pudo guardar los cambios. Inténtalo de nuevo.'
}

async function onSubmit() {
  error.value = ''
  clearSuccess()
  fieldErrors.value = {}

  const payload = {
    name: form.name.trim(),
  }

  if (form.password || form.password_confirmation) {
    payload.password = form.password
    payload.password_confirmation = form.password_confirmation
  }

  try {
    const { data } = await auth.update(payload)
    form.name = data.user.name
    form.password = ''
    form.password_confirmation = ''
    flashSuccess(data.message || 'Perfil actualizado correctamente.')
  } catch (err) {
    error.value = extractError(err)
  }
}
</script>

<template>
  <main class="settings">
    <section class="panel" aria-labelledby="settings-title">
      <h1 id="settings-title" class="panel-title">Configuraciones de Perfil</h1>
      <form class="settings-form" @submit.prevent="onSubmit">
        <label class="field">
          <span>Email</span>
          <input type="email" :value="auth.user?.email || ''" disabled />
        </label>

        <label class="field">
          <span>Nombre</span>
          <input v-model="form.name" type="text" name="name" autocomplete="name" required />
          <span v-if="fieldErrors.name" class="field-error">{{ fieldErrors.name }}</span>
        </label>

        <div class="section-divider">
          <h2 class="section-title">Contraseña</h2>
          <p class="section-sub">Déjala vacía si no quieres cambiarla.</p>
        </div>

        <label class="field">
          <span>Nueva contraseña</span>
          <input
            v-model="form.password"
            type="password"
            name="password"
            autocomplete="new-password"
          />
          <span v-if="fieldErrors.password" class="field-error">{{ fieldErrors.password }}</span>
        </label>

        <label class="field">
          <span>Confirmar contraseña</span>
          <input
            v-model="form.password_confirmation"
            type="password"
            name="password_confirmation"
            autocomplete="new-password"
          />
        </label>

        <p v-if="error" class="form-error" role="alert">{{ error }}</p>
        <p v-if="success" class="form-success" role="status">{{ success }}</p>

        <button class="submit-btn" type="submit" :disabled="auth.loading">
          {{ auth.loading ? 'Guardando…' : 'Guardar cambios' }}
        </button>
      </form>
    </section>
  </main>
</template>

<style scoped>
.settings {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.panel {
  background: var(--swc-bg-elevated);
  border: 1px solid var(--swc-border);
  border-radius: var(--swc-radius);
  padding: 1.35rem 1.4rem 1.5rem;
  box-shadow: var(--swc-shadow);
  max-width: 520px;
}

.panel-title {
  color: var(--swc-heading);
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 0.35rem;
}

.panel-sub {
  color: var(--swc-text-muted);
  font-size: 0.92rem;
  margin-bottom: 1.35rem;
}

.settings-form {
  display: flex;
  flex-direction: column;
  gap: 0.95rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  color: var(--swc-text-muted);
  font-size: 0.9rem;
}

.field input {
  appearance: none;
  width: 100%;
  border: 1px solid var(--swc-border);
  background: var(--swc-bg);
  color: var(--swc-text);
  border-radius: var(--swc-radius-sm);
  padding: 0.7rem 0.85rem;
  font: inherit;
  outline: none;
  transition: border-color 0.15s ease;
}

.field input:focus {
  border-color: var(--swc-info);
}

.field input:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.section-divider {
  margin-top: 0.35rem;
  padding-top: 0.85rem;
  border-top: 1px solid var(--swc-border);
}

.section-title {
  color: var(--swc-heading);
  font-size: 0.98rem;
  font-weight: 600;
  margin-bottom: 0.2rem;
}

.section-sub {
  color: var(--swc-text-muted);
  font-size: 0.85rem;
  margin-bottom: 0.15rem;
}

.field-error,
.form-error {
  color: var(--swc-danger);
  font-size: 0.85rem;
}

.form-success {
  color: var(--swc-success);
  font-size: 0.85rem;
}

.submit-btn {
  appearance: none;
  border: 0;
  margin-top: 0.35rem;
  align-self: flex-start;
  background: var(--swc-info);
  color: #0b1520;
  font: inherit;
  font-weight: 600;
  border-radius: var(--swc-radius-sm);
  padding: 0.75rem 1.1rem;
  cursor: pointer;
  transition: opacity 0.15s ease;
}

.submit-btn:hover:not(:disabled) {
  opacity: 0.92;
}

.submit-btn:disabled {
  opacity: 0.65;
  cursor: wait;
}
</style>
