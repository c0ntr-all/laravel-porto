import { api } from 'src/boot/axios'
import { IArtistListQuery, IJsonApiResponse } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const artistApi = {
  async getArtists(query?: IArtistListQuery): Promise<IJsonApiResponse> {
    const filters = query?.name ? buildFilterForUrl({ name: query.name }) : ''
    const response = await api.get(
      filters ? `v1/music/artists?${filters}` : 'v1/music/artists',
      {
        params: {
          include: 'tags',
          ...(query?.cursor ? { cursor: query.cursor } : {})
        }
      }
    )

    return response.data
  },
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
