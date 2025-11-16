import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'

export const tagApi = {
  async getTags(sort: string|null = null): Promise<IJsonApiResponse> {
    let url = 'v1/tags'
    if (sort) {
      url += `?sort=${sort}`
    }
    const response = await api.get(url)
    return response.data
  }
}
