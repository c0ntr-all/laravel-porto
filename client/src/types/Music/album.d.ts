import { IArtistShort } from './artist'
import { IMusicTag } from './tag'

export interface IAlbumType {
  id: string
  name: string
  slug: string
  label: string
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
  artists: IArtistShort[]
  tags: IMusicTag[]
  versions: IAlbumVersion[]
  parent?: IAlbumVersion | null
}

export interface IAlbumListQuery {
  name?: string
  artist?: string
  cursor?: string | null
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
  image_file?: File | null
}
