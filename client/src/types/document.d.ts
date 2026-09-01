export interface IPostDocumentAttachment {
  id: string
  type: string
  attachment_type: 'app_documents'
  attachment_id: string
  attachment_created_at?: string
  original_name: string
  mime_type: string
  extension: string
  size: number
  download_url: string
  created_at?: string
  updated_at?: string
}
