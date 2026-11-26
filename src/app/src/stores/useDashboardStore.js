import { defineStore } from 'pinia'
import { http as axios } from '@/api/http'

export const useDashboardStore = defineStore('dashboardStore', {
  state: () => ({
    summary: {
      sedesConectadas: 0,
      sedesTotal: 0,
      enlacesActivos: 0,
      enlacesTotal: 0,
      latenciaMediaMs: null,
      alertasActivas: 0,
    },
    links: [],
    alerts: [],
  }),

  actions: {
    async fetchSummary() {
      try {
        const resp = await axios.get('/network/summary')
        this.summary = resp.data
        return resp
      }
      catch (e) {
        throw e
      }
    },

    async fetchLinks() {
      try {
        const resp = await axios.get('/network/links')
        this.links = resp.data.data ?? []
        return resp
      }
      catch (e) {
        throw e
      }
    },

    async fetchAlerts() {
      try {
        const resp = await axios.get('/alerts')
        this.alerts = resp.data.data ?? []
        return resp
      }
      catch (e) {
        throw e
      }
    },

    async fetch() {
      try {
        const [summaryResp, linksResp, alertsResp] = await Promise.all([
          this.fetchSummary(),
          this.fetchLinks(),
          this.fetchAlerts(),
        ])
        return { summaryResp, linksResp, alertsResp }
      }
      catch (e) {
        throw e
      }
    },
  },
})
