import { IArtistShort } from './artist'
import { IMusicTag } from './tag'

export interface ITrackAlbum {
  id: string
  name: string
  edition?: string | null
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
}
