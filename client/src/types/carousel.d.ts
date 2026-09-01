export interface IImageSource {
  id: string | number
  attachment_type: string
  width: number
  height: number
  original_path: string
  original?: string
  preview_thumb_path: string
  list_thumb_path?: string
  type?: string
  description?: string | null
  album_id?: string | null
  saved_from_id?: string | null
}
