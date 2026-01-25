export interface IMediaItem {
  id: string
  type: 'photo' | 'video'
  name: string
  description: string
  original_path: string
  list_thumb_path: string
  preview_thumb_path: string
  attachment_type: string
  width: number
  height: number
}

export interface IAlbum {
  id: string
  name: string
  image: string
  description: string | null
  created_at: string
  images: {
    data: IMediaItem[]
  }
}
