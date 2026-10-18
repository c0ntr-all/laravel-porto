import { IArtistShort, IJsonApiResponse, ITrack, ITrackFormState, ITrackWriteDto } from 'src/types'
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
    cd: raw.cd == null || raw.cd === '' ? null : String(raw.cd),
    disc_id: raw.disc_id == null ? null : String(raw.disc_id),
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

export function mapTrackResponse(
  response: IJsonApiResponse,
  fallbackArtist = ''
): ITrack {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Track not found')
  }

  return normalizeTrack(raw, fallbackArtist)
}

export function mapTracksResponse(
  response: IJsonApiResponse,
  fallbackArtist = ''
): ITrack[] {
  return mapResponse(response).map(item => normalizeTrack(item, fallbackArtist))
}

function idsEqual (left: Array<string | number>, right: Array<string | number>): boolean {
  if (left.length !== right.length) {
    return false
  }

  const a = [...left].map(String).sort()
  const b = [...right].map(String).sort()

  return a.every((id, index) => id === b[index])
}

function toNullableText (value: string): string | null {
  const trimmed = value.trim()

  return trimmed === '' ? null : trimmed
}

export function mapTrackWritePatch (original: ITrack, form: ITrackFormState): ITrackWriteDto {
  const payload: ITrackWriteDto = {}
  const nextName = form.name.trim()

  if (nextName !== original.name) {
    payload.name = nextName
  }

  const nextNumberRaw = form.number.trim()
  const nextNumber = nextNumberRaw === '' ? null : Number(nextNumberRaw)
  const currentNumber = original.number ?? null
  if (nextNumberRaw !== '' && !Number.isFinite(nextNumber)) {
    // Keep invalid number out of the patch; the form should validate before save.
  } else if (nextNumber !== currentNumber) {
    payload.number = nextNumber
  }

  const nextCd = toNullableText(form.cd)
  if (nextCd !== (original.cd ?? null)) {
    payload.cd = nextCd
  }

  const nextCredits = toNullableText(form.credits)
  if (nextCredits !== (original.credits ?? null)) {
    payload.credits = nextCredits
  }

  const currentArtistIds = (original.artists ?? []).map(artist => artist.id)
  if (!idsEqual(form.artist_ids, currentArtistIds)) {
    payload.artist_ids = form.artist_ids.map(Number)
  }

  const currentTagIds = (original.tags ?? []).map(tag => tag.id)
  if (!idsEqual(form.tag_ids, currentTagIds)) {
    payload.tags = form.tag_ids.map(Number)
  }

  if (form.image_file) {
    payload.image_file = form.image_file
  }

  return payload
}

export function hasTrackWritePatch (payload: ITrackWriteDto): boolean {
  return Object.keys(payload).length > 0
}
