import { IArtistShort } from './artist'
import { IMusicTag } from './tag'
import { IAlbumType } from './album'

export interface ITrackAlbum {
  id: string
  name: string
  edition?: string | null
  album_type?: IAlbumType | null
  date?: string | null
  image?: string
}

export interface ITrack {
  id: string
  name: string
  image: string
  duration: string
  rate: number
  artist: string
  number?: number
  artists?: IArtistShort[]
  tags?: IMusicTag[]
  album?: ITrackAlbum | null
}

export interface ITrackListQuery {
  name?: string
  artist?: string
  album?: string
  cursor?: string | null
  tags?: string[]
  tags_match?: 'and' | 'or'
  tags_nested?: boolean
  rate?: number[]
  sort?: string
}
