import { api } from 'src/boot/axios'
import { ICommentCreatePayload, ICommentGetResponse, IFilter, IJsonApiResponse } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const commentApi = {
  async getComments(filters: IFilter = {}, sort = '-created_at'): Promise<ICommentGetResponse> {
    let url: string = `v1/comments?sort=${encodeURIComponent(sort)}`
    if (filters) {
      const spatieFilters = buildFilterForUrl(filters)
      url += `&${spatieFilters}`
    }
    const response = await api.get(url)

    return response.data
  },
  async createComment(payload: ICommentCreatePayload): Promise<IJsonApiResponse> {
    const response = await api.post('v1/comments', payload)

    return response.data
  },
  async updateComment(id: string, content: string): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/comments/${id}`, { content })

    return response.data
  },
  async deleteComment(id: string): Promise<{ meta?: { message?: string } }> {
    const response = await api.delete(`v1/comments/${id}`)

    return response.data
  }
}
