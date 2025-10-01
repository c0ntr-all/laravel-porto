import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'

export const tagApi = {
  async getTags(): Promise<IJsonApiResponse> {
    const response = await api.get('v1/tags?sort=-created_at')
    return response.data
  }
}
