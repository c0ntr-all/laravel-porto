import { api } from 'src/boot/axios'
import { ICommentPayload } from 'src/types'

export const commentApi = {
  async createComment(payload: ICommentPayload) {
    const response = await api.post('v1/comments', payload)

    return response.data
  }
}
