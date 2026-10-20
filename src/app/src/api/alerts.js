import { http } from './http'

export function getAlerts() {
  return http.get('/alerts')
}
