import { api } from 'src/boot/axios'
import { IJsonApiResponse, ITrackListQuery } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const trackApi = {
  async listTracks(query?: ITrackListQuery, signal?: AbortSignal): Promise<IJsonApiResponse> {
    const filters = buildFilterForUrl({
      name: query?.name,
      artist: query?.artist,
      album: query?.album
    })
    const response = await api.get(
      filters ? `v1/music/tracks?include=artists,album,tags&${filters}` : 'v1/music/tracks?include=artists,album,tags',
      {
        params: query?.cursor ? { cursor: query.cursor } : undefined,
        signal
      }
    )

    return response.data
  },

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
