import { api } from 'src/boot/axios'
import { ApiRequestContext, IJsonApiResponse } from 'src/types'
import { mapMediaItemToFormData } from 'src/api/mappers/gallery.mapper'
import { buildCorrelationHeaders } from 'src/utils/correlation'
import { IGalleryAlbumCreateDto, IGalleryAlbumUpdateDto } from 'src/types/gallery'

export const galleryApi = {
  async getAlbums(): Promise<IJsonApiResponse> {
    const response = await api.get('v1/gallery/albums')

    return response.data
  },

  async getAlbum(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/gallery/albums/${id}`)

    return response.data
  },

  async createAlbum(
    payload: IGalleryAlbumCreateDto,
    ctx?: ApiRequestContext
  ): Promise<IJsonApiResponse> {
    const response = await api.post('v1/gallery/albums', payload, {
      headers: buildCorrelationHeaders(ctx)
    })

    return response.data
  },

  async updateAlbum(
    id: string,
    payload: IGalleryAlbumUpdateDto,
    ctx?: ApiRequestContext
  ): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/gallery/albums/${id}`, payload, {
      headers: buildCorrelationHeaders(ctx)
    })

    return response.data
  },

  async upload(
    url: string,
    file: File,
    onProgress: (percent: number) => void,
    ctx?: ApiRequestContext
  ): Promise<IJsonApiResponse> {
    const formData = mapMediaItemToFormData(file)

    const response = await api.post(
      url,
      formData,
      {
        headers: buildCorrelationHeaders(ctx),
        onUploadProgress: (event) => {
          if (!event.total) return
          onProgress(Math.round(event.loaded * 100 / event.total))
        }
      }
    )

    return response.data
  },

  async uploadFromLink(
    url: string,
    link: string,
    ctx?: ApiRequestContext
  ): Promise<IJsonApiResponse> {
    const response = await api.post(url, { link }, {
      headers: buildCorrelationHeaders(ctx)
    })

    return response.data
  },

  async uploadFromPaths(
    url: string,
    paths: string[],
    ctx?: ApiRequestContext
  ): Promise<IJsonApiResponse> {
    const response = await api.post(url, { paths }, {
      headers: buildCorrelationHeaders(ctx)
    })

    return response.data
  }
}
