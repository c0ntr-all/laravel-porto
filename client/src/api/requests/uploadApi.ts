import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'

export const uploadApi = {
  async getUploads(cursor?: string | null): Promise<IJsonApiResponse> {
    const response = await api.get('v1/music/uploads', {
      params: {
        sort: '-created_at',
        include: 'artists',
        ...(cursor ? { cursor } : {})
      }
    })

    return response.data
  },

  async getUpload(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/music/uploads/${id}`, {
      params: {
        include: 'artists,albums,tracks'
      }
    })

    return response.data
  },

  async createUpload(path: string): Promise<IJsonApiResponse> {
    const response = await api.post('v1/music/uploads', { path })

    return response.data
  },

  async deleteUpload(id: string): Promise<IJsonApiResponse> {
    const response = await api.delete(`v1/music/uploads/${id}`)

    return response.data
  },

  async getLibraryFolders(path?: string): Promise<IJsonApiResponse> {
    const response = await api.get('v1/music/library/folders', {
      params: path ? { path } : {}
    })

    return response.data
  }
}
