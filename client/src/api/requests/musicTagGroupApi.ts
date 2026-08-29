import { api } from 'src/boot/axios'
import { IJsonApiResponse, IMusicTagGroupWriteDto } from 'src/types'

export const musicTagGroupApi = {
  async getGroups(): Promise<IJsonApiResponse> {
    const response = await api.get('v1/music/tag-groups')

    return response.data
  },

  async createGroup(payload: IMusicTagGroupWriteDto): Promise<IJsonApiResponse> {
    const response = await api.post('v1/music/tag-groups', payload)

    return response.data
  },

  async updateGroup(id: string, payload: IMusicTagGroupWriteDto): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/music/tag-groups/${id}`, payload)

    return response.data
  },

  async deleteGroup(id: string): Promise<{ meta?: { message?: string } }> {
    const response = await api.delete(`v1/music/tag-groups/${id}`)

    return response.data
  }
}
