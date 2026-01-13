export interface IUploadItem {
  id: string // локальный uuid
  file: File
  progress: number // 0..100
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
}

type IGalleryImageWithState = IGalleryImage & {
  is_deleted?: boolean
}
