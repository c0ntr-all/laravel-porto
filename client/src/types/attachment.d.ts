export interface IAttachment {
  id: string
  type: string
  source: string
  width: number
  height: number
  attachment_created_at: string
  attachment_type: string
  original_path: string
  list_thumb_path: string
  preview_thumb_path: string
}

export interface IAttachmentModel {
  files: File[]
}
