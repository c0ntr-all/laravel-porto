import { defineStore } from 'pinia'
import { ref } from 'vue'
import { uploadApi } from 'src/api/requests/uploadApi'
import { mapUploadResponse, mapUploadsResponse } from 'src/api/mappers/Music/upload.mapper'
import { extractCursorFromLink, handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { IMusicUpload } from 'src/types'

export const useMusicUploadStore = defineStore('musicUpload', () => {
  const uploads = ref<IMusicUpload[]>([])
  const uploadsCursor = ref<string | null>(null)
  const isUploadsLoading = ref(false)
  const isUploadsLoadingMore = ref(false)
  const detailsLoadingIds = ref<string[]>([])

  function upsertUpload(upload: IMusicUpload): void {
    const index = uploads.value.findIndex(item => item.id === upload.id)

    if (index === -1) {
      uploads.value.unshift(upload)
      return
    }

    uploads.value.splice(index, 1, {
      ...uploads.value[index],
      ...upload
    })
  }

  async function getUploads(options?: { append?: boolean }): Promise<void> {
    const append = Boolean(options?.append)

    if (append) {
      if (!uploadsCursor.value || isUploadsLoadingMore.value) {
        return
      }
      isUploadsLoadingMore.value = true
    } else {
      isUploadsLoading.value = true
      uploadsCursor.value = null
    }

    try {
      const response = await uploadApi.getUploads(append ? uploadsCursor.value : null)
      const mapped = mapUploadsResponse(response)

      uploads.value = append ? [...uploads.value, ...mapped] : mapped
      uploadsCursor.value = extractCursorFromLink(response.links?.next)
    } catch (error) {
      handleApiError(error)
    } finally {
      isUploadsLoading.value = false
      isUploadsLoadingMore.value = false
    }
  }

  async function getUpload(id: string): Promise<IMusicUpload | null> {
    const current = uploads.value.find(item => item.id === id)
    if (current?.detailsLoaded) {
      return current
    }

    if (detailsLoadingIds.value.includes(id)) {
      return current ?? null
    }

    detailsLoadingIds.value = [...detailsLoadingIds.value, id]

    try {
      const response = await uploadApi.getUpload(id)
      const mapped = mapUploadResponse(response, true)
      upsertUpload(mapped)

      return mapped
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      detailsLoadingIds.value = detailsLoadingIds.value.filter(item => item !== id)
    }
  }

  async function createUpload(path: string): Promise<IMusicUpload | null> {
    try {
      const response = await uploadApi.createUpload(path)
      const mapped = mapUploadResponse(response, true)

      upsertUpload(mapped)
      handleApiSuccess(response)

      return mapped
    } catch (error) {
      handleApiError(error)
      return null
    }
  }

  async function deleteUpload(id: string): Promise<void> {
    try {
      const response = await uploadApi.deleteUpload(id)
      uploads.value = uploads.value.filter(item => item.id !== id)
      handleApiSuccess(response)
    } catch (error) {
      handleApiError(error)
    }
  }

  function isDetailsLoading(id: string): boolean {
    return detailsLoadingIds.value.includes(id)
  }

  return {
    uploads,
    uploadsCursor,
    isUploadsLoading,
    isUploadsLoadingMore,
    getUploads,
    getUpload,
    createUpload,
    deleteUpload,
    isDetailsLoading
  }
})
