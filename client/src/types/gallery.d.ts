export type GalleryMediaKind = 'photo' | 'video'
export type GalleryMediaFilter = 'all' | GalleryMediaKind
export type GalleryUploadSource = 'device' | 'web' | 'windows'

export interface IUploadItem {
  id: string
  file: File
  progress: number
  status: 'pending' | 'uploading' | 'done' | 'error' | 'canceled'
  error?: string
}

export interface IGalleryImage {
  id: string
  type: string
  description: string | null
  source: string
  width: number
  height: number
  attachment_created_at: string
  attachment_type: string
  original_path: string
  list_thumb_path: string
  preview_thumb_path: string
  duration?: string
}

export type IGalleryImageWithState = IGalleryImage & {
  is_deleted?: boolean
}

export interface IGalleryMediaItem {
  id: string
  type: GalleryMediaKind
  name: string
  description: string | null
  original_path: string
  list_thumb_path: string
  preview_thumb_path: string
  attachment_type: string
  width: number
  height: number
  duration: string | null
}

export interface IGalleryAlbum {
  id: string
  name: string
  image: string
  description: string | null
  created_at: string
  media: IGalleryMediaItem[]
  media_count: number
}
