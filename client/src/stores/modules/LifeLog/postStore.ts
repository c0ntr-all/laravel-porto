import { defineStore } from 'pinia'
import { postApi } from 'src/api/LifeLog/postApi'
import { IPost } from 'src/types/LifeLog/post'
import { normalizeApiResponse } from 'src/utils/jsonapi'

export const usePostStore = defineStore('post', {
  state: () => ({
    posts: [] as IPost[],
    count: 0,
    isLoading: false,
    error: null as string | null
  }),

  getters: {
    getPostById: (state) => (id: number) => {
      return state.posts.find(post => post.id === id)
    }
  },

  actions: {
    async getPosts() {
      this.isLoading = true
      this.error = null
      try {
        const response = await postApi.getPosts()
        const normalizedResponse = normalizeApiResponse(response)

        this.posts = normalizedResponse.data as unknown as IPost[]
        this.count = response.meta.count
      } catch (err: any) {
        this.error = err.message ?? 'Ошибка загрузки'
      } finally {
        this.isLoading = false
      }
    },
    async createPost(id: string, payload: object) {
      const created = await postApi.createPost(id, payload)
      this.posts = this.posts.push(created)

      return created
    }
  }
})
