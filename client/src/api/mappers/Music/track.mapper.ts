import { IArtistShort, IJsonApiResponse, ITrack } from 'src/types'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { asRecords } from 'src/api/mappers/Music/helpers'
import { normalizeMusicTags } from 'src/api/mappers/Music/tag.mapper'

export function normalizeArtistShort(raw: Record<string, unknown>): IArtistShort {
  return {
    id: String(raw.id),
    name: String(raw.name ?? '')
  }
}

export function normalizeTrack(
  raw: Record<string, unknown>,
  fallbackArtist = ''
): ITrack {
  const artists = asRecords(raw.artists).map(normalizeArtistShort)
  const artistNames = artists.map(artist => artist.name).filter(Boolean)

  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    image: String(raw.image ?? ''),
    duration: String(raw.duration ?? ''),
    rate: Number(raw.rate ?? 0),
    number: raw.number == null ? undefined : Number(raw.number),
    artists,
    tags: normalizeMusicTags(raw.tags),
    artist: artistNames.join(' • ') || fallbackArtist
  }
}

export function mapTracksResponse(
  response: IJsonApiResponse,
  fallbackArtist = ''
): ITrack[] {
  return mapResponse(response).map(item => normalizeTrack(item, fallbackArtist))
}
