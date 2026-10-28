import { ITrack } from './track'

export interface IPlaylist {
  id: string
  name: string
  description: string | null
  image: string
  tracks_count?: number
  created_at?: string
  tracks: ITrack[]
}

export interface IPlaylistCreateDto {
  name: string
  description?: string | null
}

export interface IPlaylistUpdateDto {
  name?: string
  description?: string | null
  image_file?: File | null
}

export interface IPlaylistListQuery {
  name?: string
  cursor?: string | null
}
