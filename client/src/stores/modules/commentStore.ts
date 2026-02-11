import { reactive } from 'vue'
import { defineStore } from 'pinia'
import { ICommentPayload } from 'src/types'
import { normalizeEntity, upsertEntity } from 'src/utils/jsonapi'
import { StoreEntity } from 'src/types/store'
import { commentApi } from 'src/api/requests/commentApi'

export const useCommentStore = defineStore('comment', () => {
  const comments = reactive<StoreEntity<IComment>>({
    byId: {},
    allIds: []
  })

  async function createComment(payload: ICommentPayload) {
    const responseData = await commentApi.createComment(payload)
    const { entity } = normalizeEntity(responseData.data, responseData.included)

    upsertEntity(this.comments, entity)
  }
  return {
    comments,
    createComment
  }
})
