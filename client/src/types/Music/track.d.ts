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
  credits?: string | null
  image: string
  duration: string
  rate: number
  artist: string
  number?: number
  cd?: string | null
  disc_id?: string | null
  artists?: IArtistShort[]
  tags?: IMusicTag[]
  album?: ITrackAlbum | null
}

export interface ITrackWriteDto {
  name?: string
  number?: number | null
  cd?: string | null
  credits?: string | null
  artist_ids?: number[]
  tags?: number[]
  image_file?: File | null
}

export interface ITrackFormState {
  name: string
  number: string
  cd: string
  credits: string
  artist_ids: Array<string | number>
  tag_ids: Array<string | number>
  image_file?: File | null
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
