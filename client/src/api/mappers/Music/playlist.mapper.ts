import { IJsonApiResponse, IPlaylist } from 'src/types'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { asRecords } from 'src/api/mappers/Music/helpers'
import { normalizeTrack } from 'src/api/mappers/Music/track.mapper'

export function normalizePlaylist(raw: Record<string, unknown>): IPlaylist {
  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    description: raw.description == null ? null : String(raw.description),
    image: String(raw.image ?? ''),
    created_at: raw.created_at ? String(raw.created_at) : undefined,
    tracks: asRecords(raw.tracks).map(item => normalizeTrack(item))
  }
}

export function mapPlaylistResponse(response: IJsonApiResponse): IPlaylist {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Playlist not found')
  }

  return normalizePlaylist(raw)
}

export function mapPlaylistsResponse(response: IJsonApiResponse): IPlaylist[] {
  return mapResponse(response).map(normalizePlaylist)
}
