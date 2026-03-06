import { api } from 'src/boot/axios'
import { ICommentCreatePayload, ICommentGetResponse, IFilter } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const commentApi = {
  async getComments(filters: IFilter = {}): Promise<ICommentGetResponse> {
    const defaultSort: string = '-created_at'
    let url: string = `v1/comments?sort=${defaultSort}`
    if (filters) {
      const spatieFilters = buildFilterForUrl(filters)
      url += `&${spatieFilters}`
    }
    const response = await api.get(url)

    return response.data
  },
  async createComment(payload: ICommentCreatePayload) {
    const response = await api.post('v1/comments', payload)

    return response.data
  }
}
