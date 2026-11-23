import axios from 'axios'
import { getCookie, removeCookie } from '@/utils/cookies'

export const AUTH_TOKEN_COOKIE = 'swc_token'

export const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: { Accept: 'application/json' },
})

http.interceptors.request.use((config) => {
  const token = getCookie(AUTH_TOKEN_COOKIE)
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

http.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      removeCookie(AUTH_TOKEN_COOKIE)

      const requestUrl = String(error.config?.url || '')
      const isAuthBootstrap =
        requestUrl.includes('/auth/me') ||
        requestUrl.includes('/auth/login') ||
        requestUrl.includes('/auth/register')

      if (!isAuthBootstrap) {
        const { default: router } = await import('@/router')
        if (router.currentRoute.value.path !== '/login') {
          await router.push({
            path: '/login',
            query: { redirect: router.currentRoute.value.fullPath },
          })
        }
      }
    }

    return Promise.reject(error)
  },
)
