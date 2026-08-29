import { IAlbum } from 'src/types'
import { asRecords } from 'src/api/mappers/Music/helpers'
import { normalizeArtistsShort } from 'src/api/mappers/Music/artist.mapper'

export function normalizeAlbum(raw: Record<string, unknown>): IAlbum {
  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    date: raw.date == null ? null : String(raw.date),
    image: String(raw.image ?? ''),
    artists: normalizeArtistsShort(raw.artists)
  }
}

export function normalizeAlbums(value: unknown): IAlbum[] {
  return asRecords(value).map(normalizeAlbum)
}
