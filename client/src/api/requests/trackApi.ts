import { api } from 'src/boot/axios'
import { IJsonApiResponse } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const trackApi = {
  async searchTracks(
    search: string,
    cursor?: string | null,
    signal?: AbortSignal
  ): Promise<IJsonApiResponse> {
    const filters = buildFilterForUrl({ search })
    const response = await api.get(`v1/music/tracks?include=artists&${filters}`, {
      params: cursor ? { cursor } : undefined,
      signal
    })

    return response.data
  },

  async syncPlaylists(
    trackId: string,
    playlistIds: Array<string | number>
  ): Promise<{ meta?: { message?: string } }> {
    const response = await api.put(`v1/music/tracks/${trackId}/playlists`, {
      playlist_ids: playlistIds.map(Number)
    })

    return response.data
  }
}
