import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import {
  getMe,
  login as loginRequest,
  logout as logoutRequest,
  register as registerRequest,
  updateMe as updateMeRequest,
} from '@/api/auth'
import { AUTH_TOKEN_COOKIE } from '@/api/http'
import { getCookie, removeCookie, setCookie } from '@/utils/cookies'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(getCookie(AUTH_TOKEN_COOKIE))
  const initialized = ref(false)
  const loading = ref(false)

  const isAuthenticated = computed(() => Boolean(token.value && user.value))

  function persistToken(nextToken) {
    token.value = nextToken
    if (nextToken) {
      setCookie(AUTH_TOKEN_COOKIE, nextToken, { days: 7 })
    } else {
      removeCookie(AUTH_TOKEN_COOKIE)
    }
  }

  function applySession({ user: nextUser, token: nextToken }) {
    user.value = nextUser
    persistToken(nextToken)
  }

  function clearSession() {
    user.value = null
    persistToken(null)
  }

  async function initialize() {
    if (initialized.value) return

    if (!token.value) {
      clearSession()
      initialized.value = true
      return
    }

    try {
      const { data } = await getMe()
      user.value = data.user
    } catch {
      clearSession()
    } finally {
      initialized.value = true
    }
  }

  async function login(credentials) {
    loading.value = true
    try {
      const { data } = await loginRequest(credentials)
      applySession({ user: data.user, token: data.token })
      return data
    } finally {
      loading.value = false
    }
  }

  async function register(payload) {
    loading.value = true
    try {
      const { data } = await registerRequest(payload)
      applySession({ user: data.user, token: data.token })
      return data
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    loading.value = true
    try {
      if (token.value) {
        await logoutRequest()
      }
    } catch {
      // El token puede estar ya invalidado en el servidor.
    } finally {
      clearSession()
      loading.value = false
    }
  }

  async function updateProfile(payload) {
    loading.value = true
    try {
      const { data } = await updateMeRequest(payload)
      user.value = data.user
      return data
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    token,
    initialized,
    loading,
    isAuthenticated,
    initialize,
    login,
    register,
    logout,
    updateProfile,
    clearSession,
  }
})
