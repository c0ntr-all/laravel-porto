import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'

export const artistApi = {
  async getArtist(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/music/artists/${id}`)

    return response.data
  },

  async getArtistTracks(id: string, cursor?: string | null): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/music/artists/${id}/tracks`, {
      params: cursor ? { cursor } : undefined
    })

    return response.data
  }
}
