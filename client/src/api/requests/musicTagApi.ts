import { api } from 'src/boot/axios'
import { IJsonApiResponse, IMusicTagWriteDto } from 'src/types'

export const musicTagApi = {
  async getTags(): Promise<IJsonApiResponse> {
    const response = await api.get('v1/music/tags', {
      params: { include: 'tags,group' }
    })

    return response.data
  },

  async createTag(payload: IMusicTagWriteDto): Promise<IJsonApiResponse> {
    const response = await api.post('v1/music/tags', payload)

    return response.data
  },

  async updateTag(id: string, payload: IMusicTagWriteDto): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/music/tags/${id}`, payload)

    return response.data
  },

  async deleteTag(id: string): Promise<{ meta?: { message?: string } }> {
    const response = await api.delete(`v1/music/tags/${id}`)

    return response.data
  }
}
