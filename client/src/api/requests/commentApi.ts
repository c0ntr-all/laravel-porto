import { api } from 'src/boot/axios'
import { ICommentCreatePayload } from 'src/types'

export const commentApi = {
  async createComment(payload: ICommentCreatePayload) {
    const response = await api.post('v1/comments', payload)

    return response.data
  }
}
