export interface ITagShort {
  id: string
  name: string
  is_base: boolean
}
export interface ITrack {
  id: string
  name: string
  number: number
  artist: string
  image: string
  duration: string
  rate: number
  relationships: {
    tags: {
      data: ITagShort[]
    }
  }
}
export interface IAlbum {
  id: string
  name: string
  description: string
  image: string
  date: string
}
export interface AlbumVersion {
  id: string,
  name: string,
  image: string
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
