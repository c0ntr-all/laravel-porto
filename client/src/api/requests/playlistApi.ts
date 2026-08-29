import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'

export const playlistApi = {
  async getPlaylist(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/music/playlists/${id}`)

    return response.data
  },

  async listPlaylists(cursor?: string | null): Promise<IJsonApiResponse> {
    const response = await api.get('v1/music/playlists', {
      params: {
        include: 'tracks',
        ...(cursor ? { cursor } : {})
      }
    })

    return response.data
  }
}
