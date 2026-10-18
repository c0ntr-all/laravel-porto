import { IArtistShort } from './artist'
import { IMusicTag } from './tag'

export interface IAlbumType {
  id: string
  name: string
  slug: string
  label: string
}

export interface IAlbumDisc {
  id: string
  album_id?: string
  number: number
  name: string
  tracks_count?: number
}

export interface IAlbumVersion {
  id: string
  parent_id: string | null
  name: string
  edition: string | null
  album_type_id?: number | null
  album_type?: IAlbumType | null
  date: string | null
  image: string
}

export interface IAlbum {
  id: string
  parent_id: string | null
  album_type_id?: number | null
  album_type?: IAlbumType | null
  name: string
  edition: string | null
  date: string | null
  description: string | null
  image: string
  is_version?: boolean
  versions_count?: number
  discs_count?: number
  artists: IArtistShort[]
  tags: IMusicTag[]
  versions: IAlbumVersion[]
  discs: IAlbumDisc[]
  parent?: IAlbumVersion | null
}

export interface IAlbumListQuery {
  name?: string
  artist?: string
  artist_id?: Array<string | number> | string | number
  cursor?: string | null
}

export interface IAlbumDiscWriteDto {
  id: number
  name: string
}

export interface IAlbumWriteDto {
  name?: string
  description?: string | null
  date?: string | null
  edition?: string | null
  album_type_id?: number | null
  parent_id?: number | null
  artist_ids?: number[]
  tags?: number[]
  discs?: IAlbumDiscWriteDto[]
  image_file?: File | null
}

export interface IAlbumDiscFormState {
  id: string
  number: number
  name: string
  tracks_count?: number
}

export interface IAlbumFormState {
  name: string
  description: string
  edition: string
  album_type_id: string | number | null
  date: string
  parent_id: string | number | null
  artist_ids: Array<string | number>
  tag_ids: Array<string | number>
  discs: IAlbumDiscFormState[]
  image_file?: File | null
}
