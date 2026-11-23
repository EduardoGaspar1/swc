import { http } from './http'

export function login(payload) {
  return http.post('/auth/login', payload)
}

export function register(payload) {
  return http.post('/auth/register', payload)
}

export function logout() {
  return http.post('/auth/logout')
}

export function getMe() {
  return http.get('/auth/me')
}

export function updateMe(payload) {
  return http.put('/auth/me', payload)
}
