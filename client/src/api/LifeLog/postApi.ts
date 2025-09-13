import { api } from 'src/boot/axios'

export const postApi = {
  async getPosts(): Promise<object> {
    const response = await api.get('v1/lifelog/posts?sort=-datetime')
    return response.data
  },
  async createPost(payload: object): Promise<object> {
    const response = await api.post('v1/lifelog/posts', payload)
    return response.data
  }
}
