import { api } from 'src/boot/axios'
import { ApiRequestContext, IJsonApiResponse } from 'src/types'
import { mapGalleryBrowseThumbs, mapGalleryLibraryListing, mapMediaItemToFormData } from 'src/api/mappers/gallery.mapper'
import { buildCorrelationHeaders } from 'src/utils/correlation'
import {
  IGalleryAlbumCreateDto,
  IGalleryAlbumUpdateDto,
  IGalleryBrowseThumb,
  IGalleryLibraryListing,
  IGalleryTagsSyncPayload
} from 'src/types/gallery'

export const galleryApi = {
  async getAlbums(): Promise<IJsonApiResponse> {
    const response = await api.get('v1/gallery/albums')

    return response.data
  },

  async getAlbum(id: string): Promise<IJsonApiResponse> {
    const response = await api.get(`v1/gallery/albums/${id}`, {
      params: { include: 'images.tags,videos.tags,user' }
    })

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

  async saveImage(id: string, ctx?: ApiRequestContext): Promise<IJsonApiResponse> {
    const response = await api.post(`v1/gallery/images/${id}/save`, {}, {
      headers: buildCorrelationHeaders(ctx)
    })

    return response.data
  },

  async saveVideo(id: string, ctx?: ApiRequestContext): Promise<IJsonApiResponse> {
    const response = await api.post(`v1/gallery/videos/${id}/save`, {}, {
      headers: buildCorrelationHeaders(ctx)
    })

    return response.data
  },

  async updateImage(
    id: string,
    payload: { description?: string | null },
    ctx?: ApiRequestContext
  ): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/gallery/images/${id}`, payload, {
      headers: buildCorrelationHeaders(ctx)
    })

    return response.data
  },

  async updateVideo(
    id: string,
    payload: { description?: string | null },
    ctx?: ApiRequestContext
  ): Promise<IJsonApiResponse> {
    const response = await api.patch(`v1/gallery/videos/${id}`, payload, {
      headers: buildCorrelationHeaders(ctx)
    })

    return response.data
  },

  async deleteImage(id: string, albumId?: string, ctx?: ApiRequestContext): Promise<IJsonApiResponse> {
    const response = await api.delete(`v1/gallery/images/${id}`, {
      params: albumId ? { album_id: albumId } : undefined,
      headers: buildCorrelationHeaders(ctx)
    })

    return response.data
  },

  async deleteVideo(id: string, ctx?: ApiRequestContext): Promise<IJsonApiResponse> {
    const response = await api.delete(`v1/gallery/videos/${id}`, {
      headers: buildCorrelationHeaders(ctx)
    })

    return response.data
  },

  async syncImageTags(
    id: string,
    payload: IGalleryTagsSyncPayload,
    ctx?: ApiRequestContext
  ): Promise<IJsonApiResponse> {
    const response = await api.put(`v1/gallery/images/${id}/tags`, payload, {
      headers: buildCorrelationHeaders(ctx)
    })

    return response.data
  },

  async syncVideoTags(
    id: string,
    payload: IGalleryTagsSyncPayload,
    ctx?: ApiRequestContext
  ): Promise<IJsonApiResponse> {
    const response = await api.put(`v1/gallery/videos/${id}/tags`, payload, {
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
  },

  async listComputerFolder(path: string, page = 1, perPage = 60): Promise<IGalleryLibraryListing> {
    const response = await api.get('v1/gallery/library/folders', {
      params: { path, page, per_page: perPage }
    })

    return mapGalleryLibraryListing(response.data)
  },

  async queueComputerThumbs(paths: string[]): Promise<IGalleryBrowseThumb[]> {
    const response = await api.post('v1/gallery/library/thumbnails', { paths })

    return mapGalleryBrowseThumbs(response.data)
  },

  async relinkComputerFolder(path: string): Promise<{ relinked: number; still_missing: number }> {
    const response = await api.post('v1/gallery/library/relink', { path })
    const data = response.data?.data ?? {}

    return {
      relinked: Number(data.relinked ?? 0),
      still_missing: Number(data.still_missing ?? 0)
    }
  }
}
