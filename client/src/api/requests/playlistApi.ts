import { api } from 'src/boot/axios'
import { IJsonApiResponse, IPlaylistCreateDto, IPlaylistListQuery, IPlaylistUpdateDto } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

function toUpdateFormData (payload: IPlaylistUpdateDto): FormData {
  const formData = new FormData()
  formData.append('_method', 'PATCH')

  if (payload.name !== undefined) {
    formData.append('name', payload.name)
  }
  if (payload.description !== undefined) {
    formData.append('description', payload.description ?? '')
  }
  if (payload.image_file) {
    formData.append('image_file', payload.image_file)
  }

  return formData
}

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
  },

  async updatePlaylist(id: string, payload: IPlaylistUpdateDto): Promise<IJsonApiResponse> {
    const response = await api.post(`v1/music/playlists/${id}`, toUpdateFormData(payload))

    return response.data
  }
}
