import { IArtistShort } from './artist'
import { IMusicTag } from './tag'

export interface IAlbumVersion {
  id: string
  parent_id: string | null
  name: string
  edition: string | null
  date: string | null
  image: string
}

export interface IAlbum {
  id: string
  parent_id: string | null
  album_type_id?: number | null
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
  parent_id?: number | null
  artist_ids?: number[]
  tags?: number[]
  image_file?: File | null
}
