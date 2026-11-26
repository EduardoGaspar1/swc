import { defineStore } from 'pinia'
import { http as axios } from '@/api/http'

export const useEnlacesStore = defineStore('enlacesStore', {
  state: () => ({
    enlaces: [],
  }),

  actions: {
    async store(enlace) {
      try {
        return await axios.post('/enlaces', enlace)
      }
      catch (e) {
        throw e
      }
    },

    async update(enlace) {
      try {
        return await axios.put(`/enlaces/${enlace.id}`, enlace)
      }
      catch (e) {
        throw e
      }
    },

    async delete(enlace) {
      try {
        return await axios.delete(`/enlaces/${enlace.id}`)
      }
      catch (e) {
        throw e
      }
    },

    async fetch() {
      try {
        const resp = await axios.get('/enlaces')
        this.enlaces = resp.data.data ?? []
        return resp
      }
      catch (e) {
        throw e
      }
    },
  },
})
