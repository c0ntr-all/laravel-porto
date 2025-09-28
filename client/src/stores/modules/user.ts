import { defineStore } from 'pinia'
import { api } from 'src/boot/axios'

interface Data {
  email: string,
  password: string
}

export const useUserStore = defineStore({
  id: 'user',
  state: () => ({
    isAdmin: false,
    status: '',
    message: '',
    user: {}
  }),
  actions: {
    async login(data: Data): Promise<void> {
      await api.post('v1/login', data)
        .then(response => {
          localStorage.setItem('access_token', response.data.access_token)

          this.$patch({
            status: 'success',
            user: data.email
          })

          return response
        }).catch(error => {
          localStorage.removeItem('access_token')
          throw error
        })
    },
    async logout() {
      await api.post('user/logout')
        .then(response => {
          localStorage.removeItem('access_token')

          this.$patch({
            status: '',
            user: ''
          })

          return response
        })
    },
    async register(data: Data): Promise<void> {
      await api.post('v1/register', data)
        .then(() => {
          // process register
        }).catch(() => {
          // process error
        })
    }
  },
  getters: {
    isLoggedIn() {
      return !!localStorage.getItem('access_token')
    }
  }
})
