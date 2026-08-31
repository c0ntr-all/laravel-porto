import { IJsonApiResponse, ITrack } from 'src/types'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { normalizeArtistShort } from 'src/api/mappers/Music/artist.mapper'
import { asRecord, asRecords } from 'src/api/mappers/Music/helpers'
import { normalizeMusicTags } from 'src/api/mappers/Music/tag.mapper'

export function normalizeTrack(
  raw: Record<string, unknown>,
  fallbackArtist = ''
): ITrack {
  const artists = asRecords(raw.artists).map(normalizeArtistShort)
  const artistNames = artists.map(artist => artist.name).filter(Boolean)
  const albumRaw = asRecord(raw.album)

  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    image: String(raw.image ?? ''),
    duration: String(raw.duration ?? ''),
    rate: Number(raw.rate ?? 0),
    number: raw.number == null ? undefined : Number(raw.number),
    artists,
    tags: normalizeMusicTags(raw.tags),
    artist: artistNames.join(' • ') || fallbackArtist,
    album: albumRaw
      ? {
          id: String(albumRaw.id),
          name: String(albumRaw.name ?? ''),
          edition: albumRaw.edition == null ? null : String(albumRaw.edition),
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
