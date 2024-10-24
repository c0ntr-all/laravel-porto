export interface IMediaItem {
  id: string
  type: 'photo' | 'video'
  name: string
  description: string
  path: string
}

interface IAlbum {
  id: string
  name: string
  image: string
  description: string | null
  created_at: string
  relationships: {
    media: {
      data: IMediaItem[]
    }
  }
}
