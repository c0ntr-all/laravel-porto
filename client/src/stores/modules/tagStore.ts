import { defineStore } from 'pinia'
import { tagApi } from 'src/api/requests/tagApi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { ITag } from 'src/types/tag'
import { TagsModeEnum } from 'src/enums/LifeLog/TagsModeEnum'

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
    async getTags(tagsMode: TagsModeEnum|null = null): Promise<ITag[]> {
      this.isLoading = true
      this.error = null
      try {
        const sort = this.prepareSort(tagsMode)
        const response = await tagApi.getTags(sort)
        const tags = mapResponse(response) as ITag[]
        this.tags = tags
        this.count = response.meta?.count || 0

        return tags
      } catch (err: any) {
        this.error = err.message ?? 'Ошибка загрузки'
        return []
      } finally {
        this.isLoading = false
      }
    },
    prepareSort(tagsMode: TagsModeEnum|null = null) {
      const sortMap = {
        [TagsModeEnum.MOST_USED]: '-most_used',
        [TagsModeEnum.LAST]: '-created_at'
      }

      return tagsMode ? sortMap[tagsMode] : '-created_at'
    }
  }
})
