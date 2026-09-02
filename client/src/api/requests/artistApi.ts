import { api } from 'src/boot/axios'
import { IArtistListQuery, IJsonApiResponse } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

export const artistApi = {
  async getArtists(query?: IArtistListQuery): Promise<IJsonApiResponse> {
    const filters = buildFilterForUrl({
      name: query?.name,
      tags: query?.tags,
      tags_match: query?.tags?.length ? query.tags_match : undefined,
      tags_nested: query?.tags?.length
        ? (query.tags_nested ? '1' : '0')
        : undefined
    })
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

  async updateArtist(id: string, payload: {
    name?: string
    description?: string | null
    tags?: Array<string | number>
    image_file?: File | null
  }): Promise<IJsonApiResponse> {
    const formData = new FormData()
    formData.append('_method', 'PATCH')

    if (payload.name !== undefined) {
      formData.append('name', payload.name)
    }
    if (payload.description !== undefined) {
      formData.append('description', payload.description ?? '')
    }
    if (payload.tags?.length) {
      payload.tags.forEach(tagId => {
        formData.append('tags[]', String(tagId))
      })
    }
    if (payload.image_file) {
      formData.append('image_file', payload.image_file)
    }

    const response = await api.post(`v1/music/artists/${id}`, formData)

    return response.data
  },

  async getArtistTracks(id: string, cursor?: string | null): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/music/artists/${id}/tracks`, {
      params: {
        include: 'artists,album,tags',
        ...(cursor ? { cursor } : {})
      }
    })

    return response.data
  }
}
