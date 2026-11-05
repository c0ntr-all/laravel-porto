import { ITag } from './tag'

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

export interface IGalleryTagsSyncPayload {
  tags?: number[]
  new_tags?: string[]
}

export type GalleryUploadItemStatus =
  | 'pending'
  | 'uploading'
  | 'done'
  | 'finished'
  | 'error'
  | 'canceled'

export interface IUploadItem {
  id: string
  file: File
  progress: number
  status: GalleryUploadItemStatus
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
  created_at: string
  taken_at?: string | null
  thumb_status?: string
  file_missing?: boolean
  file_size?: number | null
  file_mtime?: number | null
  tags: ITag[]
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
  updated_at?: string | null
  user: IGalleryCommentAuthor | null
}

export interface IGalleryLibraryCrumb {
  name: string
  path: string
}

export interface IGalleryLibraryFolder {
  name: string
  path: string
}

export interface IGalleryLibraryFile {
  name: string
  path: string
  size: number
  mtime: number
  in_gallery: boolean
  file_missing: boolean
  thumb_url: string | null
}

export interface IGalleryLibraryListing {
  path: string
  name: string
  breadcrumbs: IGalleryLibraryCrumb[]
  folders: IGalleryLibraryFolder[]
  files: IGalleryLibraryFile[]
  page: number
  per_page: number
  total: number
  last_page: number
}

export interface IGalleryBrowseThumb {
  path: string
  status: 'ready' | 'pending' | 'missing' | string
  url: string | null
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
