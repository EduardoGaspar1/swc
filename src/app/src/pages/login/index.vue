<script setup>
import { computed, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const mode = ref('login')
const error = ref('')
const fieldErrors = ref({})

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const isRegister = computed(() => mode.value === 'register')
const title = computed(() => (isRegister.value ? 'Crear cuenta' : 'Iniciar sesión'))
const subtitle = computed(() =>
  isRegister.value
    ? 'Regístrate para acceder al panel de Smart WAN Control.'
    : 'Accede al panel de monitorización de la red WAN.',
)
const submitLabel = computed(() => {
  if (auth.loading) return isRegister.value ? 'Creando cuenta…' : 'Entrando…'
  return isRegister.value ? 'Registrarse' : 'Entrar'
})

function switchMode(next) {
  mode.value = next
  error.value = ''
  fieldErrors.value = {}
}

function extractError(err) {
  const data = err.response?.data
  if (data?.errors) {
    fieldErrors.value = Object.fromEntries(
      Object.entries(data.errors).map(([key, messages]) => [key, messages?.[0] ?? '']),
    )
  }
  return data?.message || 'No se pudo completar la operación. Inténtalo de nuevo.'
}

async function onSubmit() {
  error.value = ''
  fieldErrors.value = {}

  try {
    if (isRegister.value) {
      await auth.register({
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
      })
    } else {
      await auth.login({
        email: form.email,
        password: form.password,
      })
    }

    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/'
    await router.replace(redirect || '/')
  } catch (err) {
    error.value = extractError(err)
  }
}
</script>

<template>
  <main class="login-page">
    <section class="login-card" aria-labelledby="login-title">
      <div class="login-brand">
        <span class="brand-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.75">
            <circle cx="12" cy="12" r="9" />
            <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" />
            <circle cx="12" cy="8" r="1.25" fill="currentColor" stroke="none" />
            <circle cx="8" cy="14" r="1.25" fill="currentColor" stroke="none" />
            <circle cx="16" cy="14" r="1.25" fill="currentColor" stroke="none" />
          </svg>
        </span>
        <div>
          <p class="brand-name">WAN Smart Control</p>
          <p class="brand-tag">Monitorización WAN</p>
        </div>
      </div>

      <h1 id="login-title" class="login-title">{{ title }}</h1>
      <p class="login-subtitle">{{ subtitle }}</p>

      <div class="mode-switch" role="tablist" aria-label="Modo de acceso">
        <button
          type="button"
          role="tab"
          class="mode-btn"
          :class="{ active: !isRegister }"
          :aria-selected="!isRegister"
          @click="switchMode('login')"
        >
          Iniciar sesión
        </button>
        <button
          type="button"
          role="tab"
          class="mode-btn"
          :class="{ active: isRegister }"
          :aria-selected="isRegister"
          @click="switchMode('register')"
        >
          Registrarse
        </button>
      </div>

      <form class="login-form" @submit.prevent="onSubmit">
        <label v-if="isRegister" class="field">
          <span>Nombre</span>
          <input v-model="form.name" type="text" name="name" autocomplete="name" required />
          <span v-if="fieldErrors.name" class="field-error">{{ fieldErrors.name }}</span>
        </label>

        <label class="field">
          <span>Email</span>
          <input
            v-model="form.email"
            type="email"
            name="email"
            autocomplete="email"
            required
          />
          <span v-if="fieldErrors.email" class="field-error">{{ fieldErrors.email }}</span>
        </label>

        <label class="field">
          <span>Contraseña</span>
          <input
            v-model="form.password"
            type="password"
            name="password"
            :autocomplete="isRegister ? 'new-password' : 'current-password'"
            required
          />
          <span v-if="fieldErrors.password" class="field-error">{{ fieldErrors.password }}</span>
        </label>

        <label v-if="isRegister" class="field">
          <span>Confirmar contraseña</span>
          <input
            v-model="form.password_confirmation"
            type="password"
            name="password_confirmation"
            autocomplete="new-password"
            required
          />
        </label>

        <p v-if="error" class="form-error" role="alert">{{ error }}</p>

        <button class="submit-btn" type="submit" :disabled="auth.loading">
          {{ submitLabel }}
        </button>
      </form>
    </section>
  </main>
</template>

<style scoped>
.login-page {
  min-height: calc(100vh - 2.5rem);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 0;
}

.login-card {
  width: 100%;
  max-width: 420px;
  background: var(--swc-bg-elevated);
  border: 1px solid var(--swc-border);
  border-radius: var(--swc-radius);
  padding: 1.75rem 1.6rem 1.85rem;
  box-shadow: var(--swc-shadow);
}

.login-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.brand-icon {
  display: inline-flex;
  color: var(--swc-info);
}

.brand-name {
  color: var(--swc-heading);
  font-weight: 600;
  font-size: 1.05rem;
}

.brand-tag {
  color: var(--swc-text-muted);
  font-size: 0.85rem;
}

.login-title {
  color: var(--swc-heading);
  font-size: 1.45rem;
  font-weight: 600;
  margin-bottom: 0.35rem;
}

.login-subtitle {
  color: var(--swc-text-muted);
  font-size: 0.92rem;
  margin-bottom: 1.25rem;
}

.mode-switch {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.35rem;
  background: var(--swc-bg-soft);
  border: 1px solid var(--swc-border);
  border-radius: var(--swc-radius-sm);
  padding: 0.25rem;
  margin-bottom: 1.25rem;
}

.mode-btn {
  appearance: none;
  border: 0;
  background: transparent;
  color: var(--swc-text-muted);
  border-radius: 6px;
  padding: 0.55rem 0.75rem;
  font: inherit;
  font-size: 0.9rem;
  cursor: pointer;
}

.mode-btn.active {
  background: var(--swc-bg-elevated);
  color: var(--swc-heading);
  box-shadow: var(--swc-shadow);
}

.login-form {
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

.field-error,
.form-error {
  color: var(--swc-danger);
  font-size: 0.85rem;
}

.form-error {
  margin-top: -0.2rem;
}

.submit-btn {
  appearance: none;
  border: 0;
  margin-top: 0.35rem;
  background: var(--swc-info);
  color: #0b1520;
  font: inherit;
  font-weight: 600;
  border-radius: var(--swc-radius-sm);
  padding: 0.8rem 1rem;
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
