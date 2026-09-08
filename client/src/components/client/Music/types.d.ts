export interface IRelationshipItem {
  type: string
  id: string
  meta?: Record<string, any>
}
export interface IIncludedItem {
  type: string
  id: string
  attributes: Record<string, any>
  relationships?: any
}
export interface ITagShort {
  id: string
  name: string
  is_base: boolean
}
export interface ITrack {
  id: string
  name: string
  credits?: string | null
  number: number
  artist: string
  artists?: Array<{
    id: string
    name: string
  }>
  image: string
  duration: string
  rate: number
  relationships: {
    tags: {
      data: ITagShort[]
    }
    artists?: {
      data: Array<{
        id: string
        name: string
      }>
    }
  }
}
export interface IAlbum {
  id: string
  name: string
  description: string
  image: string
  date: string
  edition?: string | null
  album_type?: {
    id: string
    name: string
    slug: string
    label: string
  } | null
}
export interface AlbumVersion {
  id: string,
  name: string,
  image: string
  edition?: string | null
  album_type?: IAlbum['album_type']
}
export interface IArtist {
  id: string
  name: string
  image: string
  content: string
  relationships: {
    tags: {
      data: ITagShort[]
    }
  }
}
export interface IPlaylist {
  id: string
  name: string
  image: string
  created_at: string
}
