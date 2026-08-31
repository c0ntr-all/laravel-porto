import { nanoid } from 'nanoid'
import { IJsonApiResponse } from 'src/types'
import { GalleryMediaKind, IGalleryAlbum, IGalleryMediaItem, IUploadItem } from 'src/types/gallery'
import { TagsModeEnum } from 'src/enums/upload/UploadStatusEnum'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { isGalleryVideo } from 'src/utils/gallery'

function asRecords(value: unknown): Record<string, unknown>[] {
  if (!value) {
    return []
  }

  if (Array.isArray(value)) {
    return value.filter((item): item is Record<string, unknown> => (
      Boolean(item) && typeof item === 'object'
    ))
  }

  if (typeof value === 'object') {
    return [value as Record<string, unknown>]
  }

  return []
}

function mediaFromRaw(raw: Record<string, unknown>): IGalleryMediaItem[] {
  const source = raw.media ?? raw.images

  if (Array.isArray(source)) {
    return asRecords(source).map(normalizeGalleryMedia)
  }

  if (source && typeof source === 'object' && Array.isArray((source as { data?: unknown }).data)) {
    return asRecords((source as { data: unknown[] }).data).map(normalizeGalleryMedia)
  }

  return []
}

function resolveKind(raw: Record<string, unknown>): GalleryMediaKind {
  const attachmentType = String(raw.attachment_type ?? '')
  const rawType = String(raw.type ?? '')

  if (
    attachmentType === 'gallery_videos' ||
    rawType === 'video' ||
    rawType === 'gallery_videos'
  ) {
    return 'video'
  }

  return 'photo'
}

export function mapMediaItemToFormData(file: File): FormData {
  const formData = new FormData()
  formData.append('file', file)

  return formData
}

export function mapFileToUploadItem(file: File): IUploadItem {
  return {
    id: nanoid(),
    file,
    progress: 0,
    status: TagsModeEnum.PENDING
  }
}

export function normalizeGalleryMedia(raw: Record<string, unknown>): IGalleryMediaItem {
  const kind = resolveKind(raw)
  const attachmentType = String(raw.attachment_type ?? '')

  return {
    id: String(raw.id),
    type: kind,
    name: String(raw.name ?? ''),
    description: raw.description == null ? null : String(raw.description),
    original_path: String(raw.original_path ?? ''),
    list_thumb_path: String(raw.list_thumb_path ?? ''),
    preview_thumb_path: String(raw.preview_thumb_path ?? ''),
    attachment_type: attachmentType || (kind === 'video' ? 'gallery_videos' : 'gallery_images'),
    width: Number(raw.width ?? 0),
    height: Number(raw.height ?? 0),
    duration: raw.duration == null ? null : String(raw.duration)
  }
}

export function normalizeGalleryAlbum(
  raw: Record<string, unknown>,
  metaCount?: number
): IGalleryAlbum {
  const media = mediaFromRaw(raw)

  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    image: String(raw.image ?? ''),
    description: raw.description == null ? null : String(raw.description),
    created_at: String(raw.created_at ?? ''),
    media,
    media_count: Number(raw.media_count ?? metaCount ?? media.length)
  }
}

export function mapGalleryAlbumsResponse(response: IJsonApiResponse): IGalleryAlbum[] {
  return mapResponse(response).map(item => normalizeGalleryAlbum(item))
}

export function mapGalleryAlbumResponse(response: IJsonApiResponse): IGalleryAlbum {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Album not found')
  }

  return normalizeGalleryAlbum(raw, response.meta?.count)
}

export function mapGalleryMediaUploadResponse(response: IJsonApiResponse): IGalleryMediaItem[] {
  return mapResponse(response).map(normalizeGalleryMedia)
}

export function countMediaByKind(media: IGalleryMediaItem[]): { photos: number; videos: number } {
  return media.reduce(
    (acc, item) => {
      if (isGalleryVideo(item)) {
        acc.videos += 1
      } else {
        acc.photos += 1
      }

      return acc
    },
    { photos: 0, videos: 0 }
  )
}
