import { defineStore } from 'pinia'
import { http as axios, AUTH_TOKEN_COOKIE } from '@/api/http'
import { getCookie, removeCookie, setCookie } from '@/utils/cookies'

export const useUserStore = defineStore('userStore', {
  state: () => ({
    user: null,
    token: getCookie(AUTH_TOKEN_COOKIE),
    initialized: false,
    loading: false,
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.token && state.user),
    isAdmin: (state) => Number(state.user?.role) === 1,
  },

  actions: {
    persistToken(nextToken) {
      this.token = nextToken
      if (nextToken) {
        setCookie(AUTH_TOKEN_COOKIE, nextToken, { days: 7 })
      } else {
        removeCookie(AUTH_TOKEN_COOKIE)
      }
    },

    applySession({ user, token }) {
      this.user = user
      this.persistToken(token)
    },

    clearSession() {
      this.user = null
      this.persistToken(null)
    },

    async initialize() {
      if (this.initialized) return

      if (!this.token) {
        this.clearSession()
        this.initialized = true
        return
      }

      try {
        const { data } = await axios.get('/auth/me')
        this.user = data.user
      } catch {
        this.clearSession()
      } finally {
        this.initialized = true
      }
    },

    async login(credentials) {
      this.loading = true
      try {
        const resp = await axios.post('/auth/login', credentials)
        this.applySession({ user: resp.data.user, token: resp.data.token })
        return resp
      }
      catch (e) {
        throw e
      } finally {
        this.loading = false
      }
    },

    async register(payload) {
      this.loading = true
      try {
        const resp = await axios.post('/auth/register', payload)
        this.applySession({ user: resp.data.user, token: resp.data.token })
        return resp
      }
      catch (e) {
        throw e
      } finally {
        this.loading = false
      }
    },

    async logout() {
      this.loading = true
      try {
        if (this.token) {
          await axios.post('/auth/logout')
        }
      } catch {
        // El token puede estar ya invalidado en el servidor.
      } finally {
        this.clearSession()
        this.loading = false
      }
    },

    async update(payload) {
      this.loading = true
      try {
        const resp = await axios.put('/auth/me', payload)
        this.user = resp.data.user
        return resp
      }
      catch (e) {
        throw e
      } finally {
        this.loading = false
      }
    },
  },
})
