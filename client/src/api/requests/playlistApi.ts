import { api } from 'src/boot/axios'
import { IJsonApiResponse, IPlaylistCreateDto, IPlaylistListQuery } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const playlistApi = {
  async getPlaylist(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/music/playlists/${id}`, {
      params: { include: 'tracks,tracks.artists,tracks.album' }
    })

    return response.data
  },

  async listPlaylists(
    query?: IPlaylistListQuery & { include?: string }
  ): Promise<IJsonApiResponse> {
    const filters = buildFilterForUrl({
      name: query?.name
    })
    const response = await api.get(
      filters ? `v1/music/playlists?${filters}` : 'v1/music/playlists',
      {
        params: {
          ...(query?.include ? { include: query.include } : {}),
          ...(query?.cursor ? { cursor: query.cursor } : {})
        }
      }
    )

    return response.data
  },

  async createPlaylist(payload: IPlaylistCreateDto): Promise<IJsonApiResponse> {
    const response = await api.post('v1/music/playlists', payload)

    return response.data
  }
}
