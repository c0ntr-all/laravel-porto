import { IAlbum, IAlbumType, IAlbumVersion, IJsonApiResponse } from 'src/types'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { asRecord, asRecords } from 'src/api/mappers/Music/helpers'
import { normalizeArtistsShort } from 'src/api/mappers/Music/artist.mapper'
import { normalizeMusicTags } from 'src/api/mappers/Music/tag.mapper'
import { normalizeAlbumType } from 'src/utils/albumMeta'

export function normalizeAlbumVersion(raw: Record<string, unknown>): IAlbumVersion {
  return {
    id: String(raw.id),
    parent_id: raw.parent_id == null ? null : String(raw.parent_id),
    name: String(raw.name ?? ''),
    edition: raw.edition == null ? null : String(raw.edition),
    album_type_id: raw.album_type_id == null ? undefined : Number(raw.album_type_id),
    album_type: normalizeAlbumType(raw.album_type),
    date: raw.date == null ? null : String(raw.date),
    image: String(raw.image ?? '')
  }
}

export function normalizeAlbum(raw: Record<string, unknown>): IAlbum {
  const parentRaw = asRecord(raw.parent)

  return {
    id: String(raw.id),
    parent_id: raw.parent_id == null ? null : String(raw.parent_id),
    album_type_id: raw.album_type_id == null ? null : Number(raw.album_type_id),
    album_type: normalizeAlbumType(raw.album_type),
    name: String(raw.name ?? ''),
    edition: raw.edition == null ? null : String(raw.edition),
    date: raw.date == null ? null : String(raw.date),
    description: raw.description == null ? null : String(raw.description),
    image: String(raw.image ?? ''),
    is_version: Boolean(raw.is_version ?? raw.parent_id),
    versions_count: raw.versions_count == null ? undefined : Number(raw.versions_count),
    artists: normalizeArtistsShort(raw.artists),
    tags: normalizeMusicTags(raw.tags),
    versions: asRecords(raw.versions).map(normalizeAlbumVersion),
    parent: parentRaw ? normalizeAlbumVersion(parentRaw) : null
  }
}

export function normalizeAlbums(value: unknown): IAlbum[] {
  return asRecords(value).map(normalizeAlbum)
}

export function mapAlbumResponse(response: IJsonApiResponse): IAlbum {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Album not found')
  }

  return normalizeAlbum(raw)
}

export function mapAlbumsResponse(response: IJsonApiResponse): IAlbum[] {
  return mapResponse(response).map(normalizeAlbum)
}

export function mapAlbumTypesResponse(response: IJsonApiResponse): IAlbumType[] {
  return mapResponse(response)
    .map(item => normalizeAlbumType(item))
    .filter((item): item is IAlbumType => Boolean(item))
}
