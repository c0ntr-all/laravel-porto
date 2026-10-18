import { api } from 'src/boot/axios'
import { IJsonApiResponse, ITrackListQuery, ITrackWriteDto } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

function toFormData(payload: ITrackWriteDto): FormData {
  const formData = new FormData()
  formData.append('_method', 'PATCH')

  if (payload.name !== undefined) {
    formData.append('name', payload.name)
  }
  if (payload.number !== undefined) {
    formData.append('number', payload.number == null ? '' : String(payload.number))
  }
  if (payload.cd !== undefined) {
    formData.append('cd', payload.cd ?? '')
  }
  if (payload.credits !== undefined) {
    formData.append('credits', payload.credits ?? '')
  }
  payload.artist_ids?.forEach(id => {
    formData.append('artist_ids[]', String(id))
  })
  if (payload.tags !== undefined) {
    if (payload.tags.length === 0) {
      formData.append('tags', '')
    } else {
      payload.tags.forEach(id => {
        formData.append('tags[]', String(id))
      })
    }
  }
  if (payload.image_file) {
    formData.append('image_file', payload.image_file)
  }

  return formData
}

export const trackApi = {
  async listTracks(query?: ITrackListQuery, signal?: AbortSignal): Promise<IJsonApiResponse> {
    const filters = buildFilterForUrl({
      name: query?.name,
      artist: query?.artist,
      album: query?.album,
      tags: query?.tags,
      tags_match: query?.tags?.length ? query.tags_match : undefined,
      tags_nested: query?.tags?.length
        ? (query.tags_nested ? '1' : '0')
        : undefined,
      rate: query?.rate
    })
    const response = await api.get(
      filters ? `v1/music/tracks?include=artists,album,tags&${filters}` : 'v1/music/tracks?include=artists,album,tags',
      {
        params: {
          ...(query?.cursor ? { cursor: query.cursor } : {}),
          ...(query?.sort ? { sort: query.sort } : {})
        },
        signal
      }
    )

    return response.data
  },

  async getTrack(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/music/tracks/${id}`, {
      params: { include: 'artists,album,tags' }
    })

    return response.data
  },

  async updateTrack(id: string, payload: ITrackWriteDto): Promise<IJsonApiResponse> {
    const response = await api.post(`v1/music/tracks/${id}`, toFormData(payload))

    return response.data
  },

  async searchTracks(
    search: string,
    cursor?: string | null,
    signal?: AbortSignal
  ): Promise<IJsonApiResponse> {
    const filters = buildFilterForUrl({ search })
    const response = await api.get(`v1/music/tracks?include=artists,album&${filters}`, {
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
