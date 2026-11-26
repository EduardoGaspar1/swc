import { defineStore } from 'pinia'
import { http as axios } from '@/api/http'

export const useSedesStore = defineStore('sedesStore', {
  state: () => ({
    sedes: [],
  }),

  actions: {
    async store(sede) {
      try {
        return await axios.post('/sedes', sede)
      }
      catch (e) {
        throw e
      }
    },

    async update(sede) {
      try {
        return await axios.put(`/sedes/${sede.id}`, sede)
      }
      catch (e) {
        throw e
      }
    },

    async delete(sede) {
      try {
        return await axios.delete(`/sedes/${sede.id}`)
      }
      catch (e) {
        throw e
      }
    },

    async fetch() {
      try {
        const resp = await axios.get('/sedes')
        this.sedes = resp.data.data ?? []
        return resp
      }
      catch (e) {
        throw e
      }
    },
  },
})
