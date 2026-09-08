import { IAlbum } from './album'
import { IArtistShort } from './artist'

export type MusicUploadStatus =
  | 'pending'
  | 'running'
  | 'completed'
  | 'completed_with_errors'
  | 'failed'

export type MusicUploadTrackStatus =
  | 'created'
  | 'updated'
  | 'skipped'
  | 'failed'

export interface IMusicUploadTrack {
  id: string
  upload_id: string
  track_id: string | null
  album_id: string | null
  artist_id: string | null
  album_name: string | null
  track_name: string
  source_path: string
  status: MusicUploadTrackStatus
  error_message: string | null
  created_at: string | null
}

export interface IMusicUpload {
  id: string
  user_id: number
  artist_id: string | null
  artist_ids: string[]
  album_ids: string[]
  artist_name: string | null
  source_path: string
  status: MusicUploadStatus
  started_at: string | null
  finished_at: string | null
  duration_ms: number | null
  tracks_found: number
  tracks_created: number
  tracks_updated: number
  tracks_skipped: number
  tracks_failed: number
  albums_created: number
  albums_updated: number
  albums_total: number
  artists_created: number
  artists_total: number
  error_message: string | null
  created_at: string | null
  artists: IArtistShort[]
  albums: IAlbum[]
  tracks: IMusicUploadTrack[]
  detailsLoaded: boolean
}

export interface IMusicUploadProgress {
  id: string
  status: MusicUploadStatus
  stage: string
  processed?: number
  total?: number
  message?: string
  tracks_processed: number
  tracks_total: number
  albums_processed: number
  albums_total: number
  artists_processed: number
  artists_total: number
  tracks_found?: number
  tracks_created?: number
  tracks_updated?: number
  tracks_skipped?: number
  tracks_failed?: number
  albums_created?: number
  albums_updated?: number
  artists_created?: number
  artist_name?: string | null
  error_message?: string | null
}

export interface IMusicUploadAlbumGroup {
  albumId: string | null
  albumName: string
  tracks: IMusicUploadTrack[]
}
