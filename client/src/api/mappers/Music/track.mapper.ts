import { IArtistShort, IJsonApiResponse, ITrack } from 'src/types'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { normalizeArtistShort } from 'src/api/mappers/Music/artist.mapper'
import { asRecord, asRecords } from 'src/api/mappers/Music/helpers'
import { normalizeMusicTags } from 'src/api/mappers/Music/tag.mapper'
import { normalizeAlbumType } from 'src/utils/albumMeta'

type TrackArtistSource = {
  artist?: string | null
  artists?: unknown
  relationships?: unknown
}

function artistListFromUnknown(value: unknown): IArtistShort[] {
  if (!value) {
    return []
  }

  if (Array.isArray(value)) {
    return asRecords(value).map(normalizeArtistShort)
  }

  const wrapped = asRecord(value)
  if (wrapped?.data) {
    return asRecords(wrapped.data).map(normalizeArtistShort)
  }

  return asRecords(value).map(normalizeArtistShort)
}

export function listTrackArtists(track: TrackArtistSource): IArtistShort[] {
  const fromArtists = namedArtists(artistListFromUnknown(track.artists))
  if (fromArtists.length) {
    return fromArtists
  }

  return namedArtists(artistListFromUnknown(asRecord(track.relationships)?.artists))
}

export function formatTrackArtist(track: TrackArtistSource, fallback = ''): string {
  const names = listTrackArtists(track).map(artist => artist.name)
  if (names.length) {
    return names.join(' • ')
  }

  const named = typeof track.artist === 'string' ? track.artist.trim() : ''
  if (named) {
    return named
  }

  return fallback
}

function namedArtists(artists: IArtistShort[]): IArtistShort[] {
  const seen = new Set<string>()
  const unique: IArtistShort[] = []

  for (const artist of artists) {
    if (!artist.id || !artist.name || seen.has(artist.id)) {
      continue
    }

    seen.add(artist.id)
    unique.push(artist)
  }

  return unique
}

function normalizeCredits(value: unknown): string | null {
  if (typeof value !== 'string') {
    return null
  }

  const credits = value.trim()
  return credits === '' ? null : credits
}

export function normalizeTrack(
  raw: Record<string, unknown>,
  fallbackArtist = ''
): ITrack {
  const artists = listTrackArtists({ ...raw, artists: artistListFromUnknown(raw.artists) })
  const albumRaw = asRecord(raw.album)

  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    credits: normalizeCredits(raw.credits),
    image: String(raw.image ?? ''),
    duration: String(raw.duration ?? ''),
    rate: Number(raw.rate ?? 0),
    number: raw.number == null ? undefined : Number(raw.number),
    artists,
    tags: normalizeMusicTags(raw.tags),
    artist: formatTrackArtist({ ...raw, artists }, fallbackArtist),
    album: albumRaw
      ? {
          id: String(albumRaw.id),
          name: String(albumRaw.name ?? ''),
          edition: albumRaw.edition == null ? null : String(albumRaw.edition),
          album_type: normalizeAlbumType(albumRaw.album_type),
          date: albumRaw.date == null ? null : String(albumRaw.date),
          image: albumRaw.image == null ? undefined : String(albumRaw.image)
        }
      : null
  }
}

export function mapTracksResponse(
  response: IJsonApiResponse,
  fallbackArtist = ''
): ITrack[] {
  return mapResponse(response).map(item => normalizeTrack(item, fallbackArtist))
}
