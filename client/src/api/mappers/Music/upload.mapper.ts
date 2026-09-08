import {
  IJsonApiResponse,
  IMusicUpload,
  IMusicUploadAlbumGroup,
  IMusicUploadTrack,
  MusicUploadStatus,
  MusicUploadTrackStatus
} from 'src/types'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { asRecords } from 'src/api/mappers/Music/helpers'
import { normalizeArtistsShort } from 'src/api/mappers/Music/artist.mapper'
import { normalizeAlbums } from 'src/api/mappers/Music/album.mapper'

const UPLOAD_STATUSES: MusicUploadStatus[] = [
  'pending',
  'running',
  'completed',
  'completed_with_errors',
  'failed'
]

const TRACK_STATUSES: MusicUploadTrackStatus[] = [
  'created',
  'updated',
  'skipped',
  'failed'
]

function asStringIds(value: unknown): string[] {
  if (!Array.isArray(value)) {
    return []
  }

  return value.map(item => String(item))
}

function asStatus<T extends string>(value: unknown, allowed: T[], fallback: T): T {
  return allowed.includes(value as T) ? value as T : fallback
}

export function normalizeUploadTrack(raw: Record<string, unknown>): IMusicUploadTrack {
  return {
    id: String(raw.id),
    upload_id: String(raw.upload_id ?? ''),
    track_id: raw.track_id == null ? null : String(raw.track_id),
    album_id: raw.album_id == null ? null : String(raw.album_id),
    artist_id: raw.artist_id == null ? null : String(raw.artist_id),
    album_name: raw.album_name == null ? null : String(raw.album_name),
    track_name: String(raw.track_name ?? ''),
    source_path: String(raw.source_path ?? ''),
    status: asStatus(raw.status, TRACK_STATUSES, 'created'),
    error_message: raw.error_message == null ? null : String(raw.error_message),
    created_at: raw.created_at == null ? null : String(raw.created_at)
  }
}

export function normalizeUpload(
  raw: Record<string, unknown>,
  detailsLoaded = false
): IMusicUpload {
  const tracks = asRecords(raw.tracks).map(normalizeUploadTrack)
  const albums = normalizeAlbums(raw.albums)

  return {
    id: String(raw.id),
    user_id: Number(raw.user_id ?? 0),
    artist_id: raw.artist_id == null ? null : String(raw.artist_id),
    artist_ids: asStringIds(raw.artist_ids),
    album_ids: asStringIds(raw.album_ids),
    artist_name: raw.artist_name == null ? null : String(raw.artist_name),
    source_path: String(raw.source_path ?? ''),
    status: asStatus(raw.status, UPLOAD_STATUSES, 'pending'),
    started_at: raw.started_at == null ? null : String(raw.started_at),
    finished_at: raw.finished_at == null ? null : String(raw.finished_at),
    duration_ms: raw.duration_ms == null ? null : Number(raw.duration_ms),
    tracks_found: Number(raw.tracks_found ?? 0),
    tracks_created: Number(raw.tracks_created ?? 0),
    tracks_updated: Number(raw.tracks_updated ?? 0),
    tracks_skipped: Number(raw.tracks_skipped ?? 0),
    tracks_failed: Number(raw.tracks_failed ?? 0),
    albums_created: Number(raw.albums_created ?? 0),
    albums_updated: Number(raw.albums_updated ?? 0),
    albums_total: Number(raw.albums_total ?? raw.albums_created ?? 0),
    artists_created: Number(raw.artists_created ?? 0),
    artists_total: Number(raw.artists_total ?? raw.artists_created ?? 0),
    error_message: raw.error_message == null ? null : String(raw.error_message),
    created_at: raw.created_at == null ? null : String(raw.created_at),
    artists: normalizeArtistsShort(raw.artists),
    albums,
    tracks,
    detailsLoaded: detailsLoaded || albums.length > 0 || tracks.length > 0
  }
}

export function mapUploadsResponse(response: IJsonApiResponse): IMusicUpload[] {
  return mapResponse(response).map(item => normalizeUpload(item))
}

export function mapUploadResponse(response: IJsonApiResponse, detailsLoaded = true): IMusicUpload {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Upload session not found')
  }

  return normalizeUpload(raw, detailsLoaded)
}

export function groupUploadTracksByAlbum(tracks: IMusicUploadTrack[]): IMusicUploadAlbumGroup[] {
  const groups = new Map<string, IMusicUploadAlbumGroup>()

  tracks.forEach(track => {
    const key = track.album_id ?? track.album_name ?? 'unknown'
    const existing = groups.get(key)

    if (existing) {
      existing.tracks.push(track)
      return
    }

    groups.set(key, {
      albumId: track.album_id,
      albumName: track.album_name || 'Unknown album',
      tracks: [track]
    })
  })

  return Array.from(groups.values())
}
