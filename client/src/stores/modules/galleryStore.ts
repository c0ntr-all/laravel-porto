import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { IJsonApiResponse } from 'src/types'
import { galleryApi } from 'src/api/requests/galleryApi'
import {
  mapGalleryAlbumResponse,
  mapGalleryAlbumsResponse,
  mapGalleryMediaUploadResponse
} from 'src/api/mappers/gallery.mapper'
import { GalleryMediaKind, IGalleryAlbum, IGalleryMediaItem, IUploadItem } from 'src/types/gallery'
import {
  galleryUploadUrl,
  isVideoFile,
  resolveMediaKind
} from 'src/utils/gallery'

function uniqueMedia(
  current: IGalleryMediaItem[],
  incoming: IGalleryMediaItem[]
): IGalleryMediaItem[] {
  const seen = new Set(current.map(item => item.id))

  return incoming.filter(item => {
    if (seen.has(item.id)) {
      return false
    }

    seen.add(item.id)

    return true
  })
}

export const useGalleryStore = defineStore('gallery', () => {
  const albums = ref<IGalleryAlbum[]>([])
  const album = ref<IGalleryAlbum | null>(null)
  const isAlbumsLoading = ref(false)
  const isAlbumLoading = ref(false)
  const isUploading = ref(false)
  const error = ref<string | null>(null)

  const media = computed(() => album.value?.media ?? [])

  function addMedia(items: IGalleryMediaItem[]): void {
    if (!album.value || items.length === 0) {
      return
    }

    const appended = uniqueMedia(album.value.media, items)

    if (!appended.length) {
      return
    }

    album.value = {
      ...album.value,
      media: [...album.value.media, ...appended],
      media_count: album.value.media_count + appended.length
    }
  }

  async function getAlbums(): Promise<void> {
    isAlbumsLoading.value = true
    error.value = null

    try {
      const response = await galleryApi.getAlbums()
      albums.value = mapGalleryAlbumsResponse(response)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load albums'
      handleApiError(err)
    } finally {
      isAlbumsLoading.value = false
    }
  }

  async function getAlbum(id: string): Promise<IGalleryAlbum | null> {
    if (album.value?.id !== id) {
      album.value = null
    }

    isAlbumLoading.value = true
    error.value = null

    try {
      const response = await galleryApi.getAlbum(id)
      album.value = mapGalleryAlbumResponse(response)

      return album.value
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load album'
      handleApiError(err)
      return null
    } finally {
      isAlbumLoading.value = false
    }
  }

  async function uploadFiles(url: string, items: IUploadItem[]) {
    try {
      const result = []
      for (const item of items) {
        const responseData: IJsonApiResponse = await galleryApi.upload(
          url,
          item.file,
          (progress: number): void => { item.progress = progress }
        )
        const mappedResponse = mapGalleryMediaUploadResponse(responseData)

        result.push(mappedResponse[0])

        handleApiSuccess(responseData)
      }

      return result
    } catch (err: any) {
      const message = err.message ?? 'Ошибка создания'
      error.value = message
      throw new Error(message)
    }
  }

  async function uploadDeviceFiles(items: IUploadItem[]): Promise<IGalleryMediaItem[]> {
    if (!album.value) {
      return []
    }

    isUploading.value = true
    const uploaded: IGalleryMediaItem[] = []

    try {
      for (const item of items) {
        if (item.status === 'done' || item.status === 'canceled') {
          continue
        }

        item.status = 'uploading'
        item.error = undefined

        try {
          const kind: GalleryMediaKind = isVideoFile(item.file) ? 'video' : 'photo'
          const response = await galleryApi.upload(
            galleryUploadUrl(album.value.id, kind, 'device'),
            item.file,
            (progress: number) => { item.progress = progress }
          )
          const mapped = mapGalleryMediaUploadResponse(response)

          item.status = 'done'
          item.progress = 100
          uploaded.push(...mapped)
          handleApiSuccess(response)
        } catch (err) {
          item.status = 'error'
          item.error = err instanceof Error ? err.message : 'Upload failed'
          handleApiError(err)
        }
      }

      addMedia(uploaded)

      return uploaded
    } finally {
      isUploading.value = false
    }
  }

  async function uploadFromWeb(
    link: string,
    kind?: GalleryMediaKind
  ): Promise<IGalleryMediaItem[]> {
    if (!album.value) {
      return []
    }

    isUploading.value = true

    try {
      const resolvedKind = kind ?? resolveMediaKind(link)
      const response = await galleryApi.uploadFromLink(
        galleryUploadUrl(album.value.id, resolvedKind, 'web'),
        link.trim()
      )
      const mapped = mapGalleryMediaUploadResponse(response)

      addMedia(mapped)
      handleApiSuccess(response)

      return mapped
    } catch (err) {
      handleApiError(err)
      throw err
    } finally {
      isUploading.value = false
    }
  }

  async function uploadFromWindows(paths: string[]): Promise<IGalleryMediaItem[]> {
    if (!album.value || paths.length === 0) {
      return []
    }

    isUploading.value = true
    const uploaded: IGalleryMediaItem[] = []

    try {
      const groups: Record<GalleryMediaKind, string[]> = {
        photo: [],
        video: []
      }

      for (const path of paths) {
        groups[resolveMediaKind(path)].push(path)
      }

      for (const kind of ['photo', 'video'] as GalleryMediaKind[]) {
        if (!groups[kind].length) {
          continue
        }

        const response = await galleryApi.uploadFromPaths(
          galleryUploadUrl(album.value.id, kind, 'windows'),
          groups[kind]
        )
        const mapped = mapGalleryMediaUploadResponse(response)

        uploaded.push(...mapped)
        handleApiSuccess(response)
      }

      addMedia(uploaded)

      return uploaded
    } catch (err) {
      handleApiError(err)
      throw err
    } finally {
      isUploading.value = false
    }
  }

  return {
    albums,
    album,
    media,
    isAlbumsLoading,
    isAlbumLoading,
    isUploading,
    error,
    getAlbums,
    getAlbum,
    addMedia,
    uploadFiles,
    uploadDeviceFiles,
    uploadFromWeb,
    uploadFromWindows
  }
})
