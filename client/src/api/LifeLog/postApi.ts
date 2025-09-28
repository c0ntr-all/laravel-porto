import { api } from 'src/boot/axios'
import { ICreatePostPayload, IJsonApiResponse } from 'src/types'

export const postApi = {
  async getPosts(): Promise<IJsonApiResponse> {
    const response = await api.get('v1/lifelog/posts?sort=-datetime')
    return response.data
  },
  async createPost(payload: ICreatePostPayload): Promise<IJsonApiResponse> {
    const response = await api.post('v1/lifelog/posts', payload)
    return response.data
  }
}
