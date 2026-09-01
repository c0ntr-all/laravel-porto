export type GalleryMediaKind = 'photo' | 'video'
export type GalleryMediaFilter = 'all' | GalleryMediaKind
export type GalleryUploadSource = 'device' | 'web' | 'windows'
export type GallerySystemCode = 'save' | 'upload'

export interface IGalleryAlbumCreateDto {
  name: string
  description?: string | null
}

export interface IGalleryAlbumUpdateDto {
  name?: string
  description?: string | null
  image?: string | null
}

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
  original: string
  list_thumb_path: string
  preview_thumb_path: string
  attachment_type: string
  width: number
  height: number
  duration: string | null
  album_id: string | null
  saved_from_id: string | null
}

export interface IGalleryCommentAuthor {
  id: string
  name: string
  email?: string
  avatar?: string | null
}

export interface IGalleryComment {
  id: string
  content: string
  commentable_id: string
  commentable_type: string
  created_at: string
  user: IGalleryCommentAuthor | null
}

export interface IGalleryAlbum {
  id: string
  name: string
  image: string
  description: string | null
  created_at: string
  system_code: GallerySystemCode | string | null
  is_system: boolean
  media: IGalleryMediaItem[]
  media_count: number
}
