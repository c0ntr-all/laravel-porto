import { api } from 'src/boot/axios'
import { IAlbumListQuery, IAlbumWriteDto, IJsonApiResponse } from 'src/types'
import { buildFilterForUrl } from 'src/utils/jsonapi'

function toFormData(payload: IAlbumWriteDto): FormData {
  const formData = new FormData()
  formData.append('_method', 'PATCH')

  if (payload.name !== undefined) {
    formData.append('name', payload.name)
  }
  if (payload.description !== undefined) {
    formData.append('description', payload.description ?? '')
  }
  if (payload.date !== undefined) {
    formData.append('date', payload.date ?? '')
  }
  if (payload.edition !== undefined) {
    formData.append('edition', payload.edition ?? '')
  }
  if (payload.album_type_id !== undefined) {
    formData.append('album_type_id', payload.album_type_id == null ? '' : String(payload.album_type_id))
  }
  if (payload.parent_id !== undefined) {
    formData.append('parent_id', payload.parent_id == null ? '' : String(payload.parent_id))
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

export const albumApi = {
  async listAlbumTypes(): Promise<IJsonApiResponse> {
    const response = await api.get('v1/music/album-types')

    return response.data
  },

  async listAlbums(query?: IAlbumListQuery): Promise<IJsonApiResponse> {
    const filters = buildFilterForUrl({
      name: query?.name,
      artist: query?.artist
    })
    const response = await api.get(
      filters ? `v1/music/albums?${filters}` : 'v1/music/albums',
      {
        params: {
          include: 'artists,tags,versions',
          ...(query?.cursor ? { cursor: query.cursor } : {})
        }
      }
    )

    return response.data
  },

  async getAlbum(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/music/albums/${id}`, {
      params: { include: 'artists,tags,versions,parent,tracks,tracks.artists' }
    })

    return response.data
  },

  async updateAlbum(id: string, payload: IAlbumWriteDto): Promise<IJsonApiResponse> {
    const response = await api.post(`v1/music/albums/${id}`, toFormData(payload))

    return response.data
  },

  async deleteAlbum(id: string): Promise<{ meta?: { message?: string } }> {
    const response = await api.delete(`v1/music/albums/${id}`)

    return response.data
  }
}
