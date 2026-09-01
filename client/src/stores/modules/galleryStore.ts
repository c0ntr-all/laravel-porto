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
import { GalleryMediaKind, IGalleryAlbum, IGalleryAlbumCreateDto, IGalleryAlbumUpdateDto, IGalleryMediaItem, IUploadItem } from 'src/types/gallery'
import {
  galleryUploadUrl,
  getMediaOriginId,
  isGalleryVideo,
  isVideoFile,
  resolveMediaKind,
  toAlbumCoverValue
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
  const isSaving = ref(false)
  const isSavingMedia = ref(false)
  const saveAlbum = ref<IGalleryAlbum | null>(null)
  const extraSavedOriginIds = ref<Set<string>>(new Set())
  const error = ref<string | null>(null)

  const media = computed(() => album.value?.media ?? [])

  const savedOriginIds = computed(() => {
    const ids = new Set(extraSavedOriginIds.value)
    const items = saveAlbum.value?.media ?? []

    for (const item of items) {
      ids.add(item.id)

      if (item.saved_from_id) {
        ids.add(item.saved_from_id)
      }
    }

    return ids
  })

  function rememberSavedOrigin(id: string): void {
    const next = new Set(extraSavedOriginIds.value)
    next.add(id)
    extraSavedOriginIds.value = next
  }

  function mergeAlbum(current: IGalleryAlbum, incoming: IGalleryAlbum): IGalleryAlbum {
    return {
      ...current,
      ...incoming,
      media: incoming.media.length ? incoming.media : current.media,
      media_count: incoming.media.length ? incoming.media_count : current.media_count
    }
  }

  function upsertAlbum(incoming: IGalleryAlbum): void {
    if (album.value?.id === incoming.id) {
      album.value = mergeAlbum(album.value, incoming)
    }

    const index = albums.value.findIndex(item => item.id === incoming.id)

    if (index === -1) {
      albums.value = [incoming, ...albums.value]
      return
    }

    albums.value.splice(index, 1, mergeAlbum(albums.value[index] as IGalleryAlbum, incoming))
  }

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

    upsertAlbum(album.value)
  }

  function isMediaSaved(item: {
    id: string | number
    album_id?: string | null
    saved_from_id?: string | null
  }): boolean {
    if (album.value?.system_code === 'save') {
      return true
    }

    if (item.saved_from_id) {
      return true
    }

    if (saveAlbum.value && item.album_id && item.album_id === saveAlbum.value.id) {
      return true
    }

    return savedOriginIds.value.has(getMediaOriginId(item))
  }

  async function ensureSaveAlbum(): Promise<IGalleryAlbum | null> {
    if (album.value?.system_code === 'save') {
      saveAlbum.value = album.value
      return saveAlbum.value
    }

    if (saveAlbum.value) {
      return saveAlbum.value
    }

    if (!albums.value.length) {
      await getAlbums()
    }

    const listed = albums.value.find(item => item.system_code === 'save')

    if (!listed) {
      return null
    }

    try {
      const response = await galleryApi.getAlbum(listed.id)
      saveAlbum.value = mapGalleryAlbumResponse(response)

      return saveAlbum.value
    } catch (err) {
      handleApiError(err)
      return null
    }
  }

  async function saveMediaToSaveAlbum(item: IGalleryMediaItem): Promise<boolean> {
    if (isMediaSaved(item)) {
      return true
    }

    isSavingMedia.value = true

    try {
      const response = isGalleryVideo(item)
        ? await galleryApi.saveVideo(item.id)
        : await galleryApi.saveImage(item.id)
      const mapped = mapGalleryMediaUploadResponse(response)
      const copy = mapped[0]

      rememberSavedOrigin(getMediaOriginId(item))

      if (copy) {
        rememberSavedOrigin(copy.id)

        if (copy.saved_from_id) {
          rememberSavedOrigin(copy.saved_from_id)
        }
      }

      if (copy && saveAlbum.value) {
        const appended = uniqueMedia(saveAlbum.value.media, [copy])

        if (appended.length) {
          saveAlbum.value = {
            ...saveAlbum.value,
            media: [...saveAlbum.value.media, ...appended],
            media_count: saveAlbum.value.media_count + appended.length
          }
        }
      } else if (copy) {
        saveAlbum.value = {
          id: copy.album_id || 'save',
          name: 'Save',
          image: '',
          description: null,
          created_at: '',
          system_code: 'save',
          is_system: true,
          media: [copy],
          media_count: 1
        }
      }

      handleApiSuccess(response)

      return true
    } catch (err) {
      handleApiError(err)
      return false
    } finally {
      isSavingMedia.value = false
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
      upsertAlbum(album.value)

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

  async function createAlbum(payload: IGalleryAlbumCreateDto): Promise<IGalleryAlbum | null> {
    isSaving.value = true

    try {
      const response = await galleryApi.createAlbum({
        name: payload.name.trim(),
        description: payload.description?.trim() || null
      })
      const created = mapGalleryAlbumResponse(response)

      albums.value = [created, ...albums.value.filter(item => item.id !== created.id)]
      handleApiSuccess(response)

      return created
    } catch (err) {
      handleApiError(err)
      return null
    } finally {
      isSaving.value = false
    }
  }

  async function updateAlbum(payload: IGalleryAlbumUpdateDto): Promise<IGalleryAlbum | null> {
    if (!album.value) {
      return null
    }

    isSaving.value = true

    try {
      const response = await galleryApi.updateAlbum(album.value.id, payload)
      const updated = mapGalleryAlbumResponse(response)

      upsertAlbum(updated)
      handleApiSuccess(response)

      return updated
    } catch (err) {
      handleApiError(err)
      throw err
    } finally {
      isSaving.value = false
    }
  }

  async function updateCoverFromMedia(item: IGalleryMediaItem): Promise<void> {
    const cover = toAlbumCoverValue(item.list_thumb_path || item.original_path)

    if (!cover) {
      return
    }

    await updateAlbum({ image: cover })
  }

  async function updateCoverFromFile(file: File): Promise<void> {
    if (!album.value) {
      return
    }

    isUploading.value = true

    try {
      const response = await galleryApi.upload(
        galleryUploadUrl(album.value.id, 'photo', 'device'),
        file,
        () => undefined
      )
      const mapped = mapGalleryMediaUploadResponse(response)

      addMedia(mapped)

      if (mapped[0]) {
        await updateCoverFromMedia(mapped[0])
      }
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
    saveAlbum,
    media,
    isAlbumsLoading,
    isAlbumLoading,
    isUploading,
    isSaving,
    isSavingMedia,
    error,
    getAlbums,
    getAlbum,
    addMedia,
    createAlbum,
    updateAlbum,
    updateCoverFromMedia,
    updateCoverFromFile,
    ensureSaveAlbum,
    isMediaSaved,
    saveMediaToSaveAlbum,
    uploadFiles,
    uploadDeviceFiles,
    uploadFromWeb,
    uploadFromWindows
  }
})
