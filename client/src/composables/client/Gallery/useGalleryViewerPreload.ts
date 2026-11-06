import { nextTick, onUnmounted, reactive, ref, watch, type Ref } from 'vue'
import { IImageSource } from 'src/types/carousel'
import { getMediaOriginalUrl, isGalleryVideo, resolveMediaUrl } from 'src/utils/gallery'

const PRELOAD_AHEAD = 8
const PRELOAD_BEHIND = 2
const PRELOAD_CONCURRENCY = 3

function windowBounds (index: number, dir: 1 | -1, length: number): [number, number] {
  if (index < 0 || length === 0) {
    return [0, -1]
  }

  const before = dir > 0 ? PRELOAD_BEHIND : PRELOAD_AHEAD
  const after = dir > 0 ? PRELOAD_AHEAD : PRELOAD_BEHIND

  return [
    Math.max(0, index - before),
    Math.min(length - 1, index + after)
  ]
}

function isAbortError (error: unknown): boolean {
  return error instanceof DOMException && error.name === 'AbortError'
}

function thumbSrc (slide: IImageSource, video: boolean): string {
  const path = video
    ? (slide.list_thumb_path || slide.preview_thumb_path)
    : (slide.preview_thumb_path || slide.list_thumb_path)

  return resolveMediaUrl(path)
}

export function useGalleryViewerPreload<T extends IImageSource> (
  show: Ref<boolean | undefined>,
  slides: Ref<T[]>,
  currentIndex: Ref<number>
): {
  prefetchVideoSrc: Ref<string>
  frameSrc: (slide: T, index: number) => string
  originalSrc: (slide: T) => string
} {
  const direction = ref<1 | -1>(1)
  const prefetchVideoSrc = ref('')
  const photoSrcById = reactive(new Map<string, string>())
  const blobIds = new Set<string>()
  const failedIds = new Set<string>()
  const inflight = new Map<string, AbortController>()
  let generation = 0

  function inWindow (index: number): boolean {
    const [start, end] = windowBounds(currentIndex.value, direction.value, slides.value.length)

    return index >= start && index <= end
  }

  function wantedPhotos (): Map<string, T> {
    const [start, end] = windowBounds(currentIndex.value, direction.value, slides.value.length)
    const wanted = new Map<string, T>()

    for (let index = start; index <= end; index += 1) {
      const slide = slides.value[index]

      if (!slide || isGalleryVideo(slide) || slide.file_missing) {
        continue
      }

      if (!getMediaOriginalUrl(slide)) {
        continue
      }

      wanted.set(String(slide.id), slide)
    }

    return wanted
  }

  function dropUnwanted (wanted: Map<string, T>): void {
    for (const id of [...photoSrcById.keys()]) {
      if (wanted.has(id)) {
        continue
      }

      const url = photoSrcById.get(id)

      if (url && blobIds.has(id)) {
        URL.revokeObjectURL(url)
        blobIds.delete(id)
      }

      photoSrcById.delete(id)
    }

    for (const [id, controller] of inflight) {
      if (wanted.has(id)) {
        continue
      }

      controller.abort()
      inflight.delete(id)
    }
  }

  function rememberDirect (id: string, slide: T): void {
    const url = getMediaOriginalUrl(slide)

    if (url) {
      photoSrcById.set(id, url)
    }
  }

  function pumpPhotos (): void {
    if (!show.value) {
      return
    }

    const wanted = wantedPhotos()
    dropUnwanted(wanted)

    const index = currentIndex.value
    const [start, end] = windowBounds(index, direction.value, slides.value.length)
    const queue: Array<{ id: string, distance: number, url: string }> = []

    for (let position = start; position <= end; position += 1) {
      const slide = slides.value[position]

      if (!slide) {
        continue
      }

      const id = String(slide.id)

      if (!wanted.has(id) || photoSrcById.has(id) || inflight.has(id)) {
        continue
      }

      if (failedIds.has(id)) {
        rememberDirect(id, slide)
        continue
      }

      const url = getMediaOriginalUrl(slide)

      if (!url) {
        continue
      }

      queue.push({
        id,
        distance: Math.abs(position - index),
        url
      })
    }

    queue.sort((left, right) => left.distance - right.distance)

    for (const job of queue) {
      if (inflight.size >= PRELOAD_CONCURRENCY) {
        break
      }

      const controller = new AbortController()
      inflight.set(job.id, controller)
      void loadPhoto(job.id, job.url, controller.signal)
    }
  }

  async function loadPhoto (id: string, url: string, signal: AbortSignal): Promise<void> {
    try {
      const response = await fetch(url, { signal })

      if (!response.ok) {
        throw new Error('media preload failed')
      }

      const blob = await response.blob()

      if (signal.aborted || !show.value || !wantedPhotos().has(id)) {
        return
      }

      const objectUrl = URL.createObjectURL(blob)

      if (signal.aborted || !show.value || !wantedPhotos().has(id)) {
        URL.revokeObjectURL(objectUrl)
        return
      }

      const previous = photoSrcById.get(id)

      if (previous && blobIds.has(id)) {
        URL.revokeObjectURL(previous)
      }

      photoSrcById.set(id, objectUrl)
      blobIds.add(id)
    } catch (error) {
      if (signal.aborted || isAbortError(error)) {
        return
      }

      failedIds.add(id)

      const slide = slides.value.find(item => String(item.id) === id)

      if (show.value && slide && wantedPhotos().has(id)) {
        rememberDirect(id, slide)
      }
    } finally {
      inflight.delete(id)

      if (show.value && !signal.aborted) {
        pumpPhotos()
      }
    }
  }

  function nearestVideoUrl (): string {
    const index = currentIndex.value
    const dir = direction.value
    const [start, end] = windowBounds(index, dir, slides.value.length)
    const step = dir > 0 ? 1 : -1

    for (let position = index + step; position >= start && position <= end; position += step) {
      const slide = slides.value[position]

      if (slide && isGalleryVideo(slide) && !slide.file_missing) {
        return getMediaOriginalUrl(slide)
      }
    }

    return ''
  }

  async function schedulePreload (): Promise<void> {
    const ticket = ++generation
    pumpPhotos()
    await nextTick()

    if (ticket !== generation || !show.value) {
      return
    }

    prefetchVideoSrc.value = nearestVideoUrl()
  }

  function resetPreload (): void {
    generation += 1
    prefetchVideoSrc.value = ''

    for (const controller of inflight.values()) {
      controller.abort()
    }

    inflight.clear()

    for (const id of blobIds) {
      const url = photoSrcById.get(id)

      if (url) {
        URL.revokeObjectURL(url)
      }
    }

    blobIds.clear()
    photoSrcById.clear()
    failedIds.clear()
  }

  function frameSrc (slide: T, index: number): string {
    if (!inWindow(index)) {
      return ''
    }

    const video = isGalleryVideo(slide)

    if (video) {
      return thumbSrc(slide, true)
    }

    return photoSrcById.get(String(slide.id)) || thumbSrc(slide, false)
  }

  function originalSrc (slide: T): string {
    return getMediaOriginalUrl(slide)
  }

  watch([show, currentIndex], ([open, index], previous) => {
    if (!open) {
      return
    }

    const wasOpen = previous?.[0]
    const previousIndex = previous?.[1]

    if (!wasOpen) {
      direction.value = 1
      return
    }

    if (typeof previousIndex === 'number' && previousIndex >= 0 && index !== previousIndex) {
      direction.value = index > previousIndex ? 1 : -1
    }
  })

  watch([show, currentIndex, direction, slides], () => {
    if (!show.value) {
      resetPreload()
      return
    }

    void schedulePreload()
  })

  onUnmounted(() => {
    resetPreload()
  })

  return {
    prefetchVideoSrc,
    frameSrc,
    originalSrc
  }
}
