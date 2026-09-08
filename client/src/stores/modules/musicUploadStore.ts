import { defineStore } from 'pinia'
import { ref } from 'vue'
import { uploadApi } from 'src/api/requests/uploadApi'
import { mapUploadResponse, mapUploadsResponse } from 'src/api/mappers/Music/upload.mapper'
import { extractCursorFromLink, handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { subscribeMusicUploadRealtime } from 'src/services/realtime/echo.client'
import { IMusicUpload, IMusicUploadProgress, MusicUploadStatus } from 'src/types'

const ACTIVE_STATUSES: MusicUploadStatus[] = ['pending', 'running']

function emptyProgress(partial: Partial<IMusicUploadProgress> = {}): IMusicUploadProgress {
  return {
    id: '',
    status: 'pending',
    stage: 'started',
    processed: 0,
    total: 0,
    message: '',
    tracks_processed: 0,
    tracks_total: 0,
    albums_processed: 0,
    albums_total: 0,
    artists_processed: 0,
    artists_total: 0,
    ...partial
  }
}

function progressFromUpload(upload: IMusicUpload): IMusicUploadProgress {
  const tracksProcessed = upload.tracks_created + upload.tracks_updated + upload.tracks_skipped + upload.tracks_failed

  return emptyProgress({
    id: upload.id,
    status: upload.status,
    stage: ACTIVE_STATUSES.includes(upload.status) ? 'running' : 'finished',
    processed: tracksProcessed,
    total: upload.tracks_found,
    tracks_processed: tracksProcessed,
    tracks_total: upload.tracks_found,
    albums_processed: upload.albums_created + upload.albums_updated,
    albums_total: upload.albums_total || upload.albums_created + upload.albums_updated,
    artists_processed: upload.artists_created,
    artists_total: upload.artists_total || upload.artists.length || upload.artists_created,
    tracks_found: upload.tracks_found,
    tracks_created: upload.tracks_created,
    albums_created: upload.albums_created,
    artists_created: upload.artists_created,
    artist_name: upload.artist_name,
    error_message: upload.error_message
  })
}

function mergeProgress(
  current: IMusicUploadProgress | null,
  incoming: Partial<IMusicUploadProgress> & { id?: string }
): IMusicUploadProgress {
  const base = current ?? emptyProgress({ id: incoming.id })

  return {
    ...base,
    ...incoming,
    id: String(incoming.id ?? base.id),
    tracks_processed: Number(incoming.tracks_processed ?? base.tracks_processed),
    tracks_total: Number(incoming.tracks_total ?? base.tracks_total),
    albums_processed: Number(incoming.albums_processed ?? base.albums_processed),
    albums_total: Number(incoming.albums_total ?? base.albums_total),
    artists_processed: Number(incoming.artists_processed ?? base.artists_processed),
    artists_total: Number(incoming.artists_total ?? base.artists_total),
    processed: Number(incoming.processed ?? incoming.tracks_processed ?? base.processed),
    total: Number(incoming.total ?? incoming.tracks_total ?? base.total)
  }
}

export const useMusicUploadStore = defineStore('musicUpload', () => {
  const uploads = ref<IMusicUpload[]>([])
  const uploadsCursor = ref<string | null>(null)
  const isUploadsLoading = ref(false)
  const isUploadsLoadingMore = ref(false)
  const detailsLoadingIds = ref<string[]>([])
  const activeUploadId = ref<string | null>(null)
  const activeProgress = ref<IMusicUploadProgress | null>(null)

  let unsubscribeEcho: (() => void) | undefined
  let pollTimer: ReturnType<typeof setInterval> | undefined

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

  function applyProgress(payload: Partial<IMusicUploadProgress> & { id?: string }): void {
    activeProgress.value = mergeProgress(activeProgress.value, payload)

    const current = uploads.value.find(item => item.id === String(payload.id ?? activeProgress.value?.id))
    if (!current || !payload.status) {
      return
    }

    upsertUpload({
      ...current,
      status: payload.status,
      tracks_found: payload.tracks_total ?? current.tracks_found,
      albums_total: payload.albums_total ?? current.albums_total,
      artists_total: payload.artists_total ?? current.artists_total,
      error_message: payload.error_message === undefined ? current.error_message : payload.error_message
    })
  }

  function stopWatching(): void {
    unsubscribeEcho?.()
    unsubscribeEcho = undefined

    if (pollTimer) {
      clearInterval(pollTimer)
      pollTimer = undefined
    }
  }

  async function refreshWatchedUpload(): Promise<void> {
    if (!activeUploadId.value) {
      return
    }

    const upload = await getUpload(activeUploadId.value, { force: true })
    if (!upload) {
      return
    }

    applyProgress(progressFromUpload(upload))

    if (!ACTIVE_STATUSES.includes(upload.status)) {
      stopWatching()
    }
  }

  function watchUpload(id: string): void {
    if (activeUploadId.value === id && unsubscribeEcho) {
      return
    }

    stopWatching()
    activeUploadId.value = id

    const current = uploads.value.find(item => item.id === id)
    if (current) {
      applyProgress(progressFromUpload(current))
    } else if (!activeProgress.value || activeProgress.value.id !== id) {
      applyProgress(emptyProgress({ id, status: 'pending', stage: 'started', message: 'Starting upload…' }))
    }

    unsubscribeEcho = subscribeMusicUploadRealtime(id, {
      onStarted: payload => applyProgress({ ...payload, stage: payload.stage || 'started' }),
      onProgressed: payload => applyProgress(payload),
      onFinished: payload => {
        applyProgress({
          ...payload,
          stage: 'finished',
          tracks_processed: payload.tracks_total ?? payload.tracks_found ?? activeProgress.value?.tracks_processed,
          albums_processed: payload.albums_total ?? payload.albums_created ?? activeProgress.value?.albums_processed,
          artists_processed: payload.artists_total ?? payload.artists_created ?? activeProgress.value?.artists_processed
        })
        void refreshWatchedUpload()
      }
    })

    pollTimer = setInterval(() => {
      void refreshWatchedUpload()
    }, 2000)
  }

  function watchLatestActiveUpload(): void {
    const running = uploads.value.find(item => ACTIVE_STATUSES.includes(item.status))
    if (running) {
      watchUpload(running.id)
    }
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
      watchLatestActiveUpload()
    } catch (error) {
      handleApiError(error)
    } finally {
      isUploadsLoading.value = false
      isUploadsLoadingMore.value = false
    }
  }

  async function getUpload(id: string, options?: { force?: boolean }): Promise<IMusicUpload | null> {
    const current = uploads.value.find(item => item.id === id)
    if (current?.detailsLoaded && !options?.force) {
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
    applyProgress(emptyProgress({
      status: 'pending',
      stage: 'started',
      message: 'Starting upload…'
    }))

    try {
      const response = await uploadApi.createUpload(path)
      const mapped = mapUploadResponse(response, true)

      upsertUpload(mapped)
      handleApiSuccess(response)

      if (ACTIVE_STATUSES.includes(mapped.status)) {
        watchUpload(mapped.id)
      } else {
        stopWatching()
        applyProgress(progressFromUpload(mapped))
      }

      return mapped
    } catch (error) {
      activeProgress.value = null
      handleApiError(error)
      return null
    }
  }

  async function deleteUpload(id: string): Promise<void> {
    try {
      const response = await uploadApi.deleteUpload(id)
      uploads.value = uploads.value.filter(item => item.id !== id)
      if (activeUploadId.value === id) {
        stopWatching()
        activeUploadId.value = null
        activeProgress.value = null
      }
      handleApiSuccess(response)
    } catch (error) {
      handleApiError(error)
    }
  }

  function isDetailsLoading(id: string): boolean {
    return detailsLoadingIds.value.includes(id)
  }

  function clearProgress(): void {
    stopWatching()
    activeUploadId.value = null
    activeProgress.value = null
  }

  return {
    uploads,
    uploadsCursor,
    isUploadsLoading,
    isUploadsLoadingMore,
    activeUploadId,
    activeProgress,
    getUploads,
    getUpload,
    createUpload,
    deleteUpload,
    isDetailsLoading,
    watchUpload,
    watchLatestActiveUpload,
    clearProgress,
    stopWatching
  }
})
