import { defineStore } from 'pinia'
import { tagApi } from 'src/api/requests/tagApi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { ITag } from 'src/types/tag'

export const useTagStore = defineStore('tag', {
  state: () => ({
    tags: [] as ITag[],
    count: 0,
    isLoading: false,
    error: null as string | null
  }),

  getters: {
    getTagById: (state) => (id: string) => {
      return state.tags.find(tag => tag.id === id)
    }
  },

  actions: {
    async getTags() {
      this.isLoading = true
      this.error = null
      try {
        const response = await tagApi.getTags()
        const tags = mapResponse(response) as ITag[]
        this.tags = tags
        this.count = response.meta?.count || 0

        return tags
      } catch (err: any) {
        this.error = err.message ?? 'Ошибка загрузки'
      } finally {
        this.isLoading = false
      }
    }
  }
})
