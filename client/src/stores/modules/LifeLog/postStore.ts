import { defineStore } from 'pinia'
import { postApi } from 'src/api/LifeLog/postApi'
import { ICreatePostPayload, IPost } from 'src/types/LifeLog/post'
import { handleApiSuccess } from 'src/utils/jsonapi'
import { mapResponse } from 'src/utils/jsonApiMapper'

export const usePostStore = defineStore('post', {
  state: () => ({
    posts: [] as IPost[],
    count: 0,
    isLoading: false,
    error: null as string | null
  }),

  getters: {
    getPostById: (state) => (id: string) => {
      return state.posts.find(post => post.id === id)
    }
  },

  actions: {
    async getPosts() {
      this.isLoading = true
      this.error = null
      try {
        const response = await postApi.getPosts()
        this.posts = mapResponse(response) as IPost[]
        this.count = response.meta?.count || 0
      } catch (err: any) {
        this.error = err.message ?? 'Ошибка загрузки'
      } finally {
        this.isLoading = false
      }
    },
    async createPost(payload: ICreatePostPayload) {
      try {
        const responseData = await postApi.createPost(payload)
        const mappedResponse = mapResponse(responseData) as IPost[]
        this.posts.unshift(...mappedResponse)
        this.count += 1

        handleApiSuccess(responseData)

        return mappedResponse
      } catch (err: any) {
        this.error = err.message ?? 'Ошибка создания'
      }
    }
  }
})
