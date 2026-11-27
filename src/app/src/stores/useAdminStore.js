import { defineStore } from 'pinia'
import { http as axios } from '@/api/http'

export const useAdminStore = defineStore('adminStore', {
  state: () => ({
    companies: [],
    users: [],
  }),

  actions: {
    async fetchCompanies() {
      const resp = await axios.get('/admin/companies')
      this.companies = resp.data.data ?? []
      return resp
    },

    async storeCompany(payload) {
      return axios.post('/admin/companies', payload)
    },

    async deleteCompany(company) {
      return axios.delete(`/admin/companies/${company.id}`)
    },

    async fetchUsers() {
      const resp = await axios.get('/admin/users')
      this.users = resp.data.data ?? []
      return resp
    },

    async storeUser(payload) {
      return axios.post('/admin/users', payload)
    },

    async updateUser(user) {
      return axios.put(`/admin/users/${user.id}`, user)
    },

    async deleteUser(user) {
      return axios.delete(`/admin/users/${user.id}`)
    },
  },
})
