import { http } from './http'

export function getSummary() {
  return http.get('/network/summary')
}

export function getLinks() {
  return http.get('/network/links')
}
