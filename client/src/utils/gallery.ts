import { GalleryMediaKind, GalleryUploadSource } from 'src/types/gallery'

const VIDEO_EXTENSIONS = new Set([
  'mp4', 'webm', 'mov', 'avi', 'mkv', 'm4v', 'ogv', 'mpeg', 'mpg', 'wmv'
])

const IMAGE_EXTENSIONS = new Set([
  'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif', 'heic', 'heif'
])

export const GALLERY_MEDIA_ACCEPT = 'image/*,video/*'
export const GALLERY_IMAGE_ACCEPT = 'image/*'

export function getPathExtension(path: string): string {
  const clean = path.split('?')[0].split('#')[0]
  const base = clean.split(/[\\/]/).pop() ?? ''
  const dot = base.lastIndexOf('.')

  return dot >= 0 ? base.slice(dot + 1).toLowerCase() : ''
}

export function getMediaKindFromPath(path: string): GalleryMediaKind | 'unknown' {
  const extension = getPathExtension(path)

  if (VIDEO_EXTENSIONS.has(extension)) {
    return 'video'
  }

  if (IMAGE_EXTENSIONS.has(extension)) {
    return 'photo'
  }

  return 'unknown'
}

export function isGalleryVideo(item: { type?: string; attachment_type?: string }): boolean {
  return item.attachment_type === 'gallery_videos' || item.type === 'video'
}

export function isVideoFile(file: File): boolean {
  if (file.type.startsWith('video/')) {
    return true
  }

  if (file.type.startsWith('image/')) {
    return false
  }

  return getMediaKindFromPath(file.name) === 'video'
}

export function isMediaFile(file: File): boolean {
  return file.type.startsWith('image/') ||
    file.type.startsWith('video/') ||
    getMediaKindFromPath(file.name) !== 'unknown'
}

export function joinLocalPath(folder: string, fileName: string): string {
  const trimmed = folder.trim().replace(/[\\/]+$/, '')
  const separator = folder.includes('\\') && !folder.startsWith('/') ? '\\' : '/'

  return `${trimmed}${separator}${fileName}`
}

export function galleryUploadUrl(
  albumId: string,
  kind: GalleryMediaKind,
  source: GalleryUploadSource
): string {
  const resource = kind === 'video' ? 'videos' : 'images'

  if (source === 'web') {
    return `v1/gallery/albums/${albumId}/${resource}/upload-web`
  }

  if (source === 'windows') {
    return `v1/gallery/albums/${albumId}/${resource}/upload-windows`
  }

  return `v1/gallery/albums/${albumId}/${resource}/upload`
}

export function resolveMediaKind(path: string, fallback: GalleryMediaKind = 'photo'): GalleryMediaKind {
  const detected = getMediaKindFromPath(path)

  return detected === 'unknown' ? fallback : detected
}

export function formatMediaDuration(duration: string | null | undefined): string | null {
  if (!duration) {
    return null
  }

  const match = duration.match(/(?:(\d+):)?(\d{1,2}):(\d{2})/)

  if (!match) {
    return duration
  }

  const hours = Number(match[1] || 0)
  const minutes = Number(match[2])
  const seconds = match[3]

  if (hours > 0) {
    return `${hours}:${String(minutes).padStart(2, '0')}:${seconds}`
  }

  return `${minutes}:${seconds}`
}

export function isHttpUrl(value: string): boolean {
  try {
    const url = new URL(value.trim())

    return url.protocol === 'http:' || url.protocol === 'https:'
  } catch {
    return false
  }
}

export function isSystemGalleryAlbum(album: { system_code?: string | null; is_system?: boolean }): boolean {
  return Boolean(album.is_system || album.system_code)
}

export function hasAlbumCover(image: string | null | undefined): boolean {
  if (!image) {
    return false
  }

  return !image.includes('no-image.jpg')
}

export function toAlbumCoverValue(url: string): string {
  const trimmed = url.trim()
  const marker = '/storage/'
  const index = trimmed.indexOf(marker)

  if (index >= 0) {
    return trimmed.slice(index + marker.length)
  }

  return trimmed
}

export function resolveMediaUrl(path?: string | null): string {
  if (!path) return ''

  const trimmed = path.trim()
  if (!trimmed) return ''
  if (isHttpUrl(trimmed)) return trimmed

  const apiHost = (process.env.host ?? '').replace(/\/$/, '')
  const siteOrigin = apiHost.replace(/\/api$/, '')

  if (trimmed.startsWith('/')) {
    return `${siteOrigin}${trimmed}`
  }

  return `${siteOrigin}/${trimmed}`
}

export function getAttachmentThumbSrc(attachment: {
  list_thumb_path?: string | null
  preview_thumb_path?: string | null
  original_path?: string | null
}): string {
  const candidates = [
    attachment.list_thumb_path,
    attachment.preview_thumb_path,
    attachment.original_path
  ]

  for (const candidate of candidates) {
    const resolved = resolveMediaUrl(candidate)
    if (resolved) return resolved
  }

  return ''
}
