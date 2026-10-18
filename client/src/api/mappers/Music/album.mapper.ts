import {
  IAlbum,
  IAlbumDisc,
  IAlbumFormState,
  IAlbumType,
  IAlbumVersion,
  IAlbumWriteDto,
  IJsonApiResponse
} from 'src/types'
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

export function normalizeAlbumDisc(raw: Record<string, unknown>): IAlbumDisc {
  return {
    id: String(raw.id),
    album_id: raw.album_id == null ? undefined : String(raw.album_id),
    number: Number(raw.number ?? 1),
    name: String(raw.name ?? ''),
    tracks_count: raw.tracks_count == null ? undefined : Number(raw.tracks_count)
  }
}

export function normalizeAlbum(raw: Record<string, unknown>): IAlbum {
  const parentRaw = asRecord(raw.parent)
  const discs = asRecords(raw.discs)
    .map(normalizeAlbumDisc)
    .sort((left, right) => left.number - right.number)

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
    discs_count: raw.discs_count == null ? discs.length : Number(raw.discs_count),
    artists: normalizeArtistsShort(raw.artists),
    tags: normalizeMusicTags(raw.tags),
    versions: asRecords(raw.versions).map(normalizeAlbumVersion),
    discs,
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

function idsEqual (left: Array<string | number>, right: Array<string | number>): boolean {
  if (left.length !== right.length) {
    return false
  }

  const a = [...left].map(String).sort()
  const b = [...right].map(String).sort()

  return a.every((id, index) => id === b[index])
}

function toNullableId (value: string | number | null | undefined): number | null {
  if (value == null || value === '') {
    return null
  }

  const id = Number(value)

  return Number.isFinite(id) ? id : null
}

function toNullableText (value: string): string | null {
  const trimmed = value.trim()

  return trimmed === '' ? null : trimmed
}

function discsEqual (
  left: Array<{ id: string; name: string }>,
  right: Array<{ id: string; name: string }>
): boolean {
  if (left.length !== right.length) {
    return false
  }

  const sortedLeft = [...left].sort((a, b) => a.id.localeCompare(b.id))
  const sortedRight = [...right].sort((a, b) => a.id.localeCompare(b.id))

  return sortedLeft.every((disc, index) => (
    disc.id === sortedRight[index]?.id &&
    disc.name.trim() === (sortedRight[index]?.name ?? '').trim()
  ))
}

export function mapAlbumWritePatch (original: IAlbum, form: IAlbumFormState): IAlbumWriteDto {
  const payload: IAlbumWriteDto = {}
  const nextName = form.name.trim()

  if (nextName !== original.name) {
    payload.name = nextName
  }

  const nextDescription = toNullableText(form.description)
  if (nextDescription !== (original.description ?? null)) {
    payload.description = nextDescription
  }

  const nextEdition = toNullableText(form.edition)
  if (nextEdition !== (original.edition ?? null)) {
    payload.edition = nextEdition
  }

  const nextTypeId = toNullableId(form.album_type_id)
  const currentTypeId = toNullableId(original.album_type?.id ?? original.album_type_id)
  if (nextTypeId !== currentTypeId) {
    payload.album_type_id = nextTypeId
  }

  const nextDate = form.date.trim() || null
  if (nextDate !== (original.date ?? null)) {
    payload.date = nextDate
  }

  const nextParentId = toNullableId(form.parent_id)
  const currentParentId = toNullableId(original.parent_id)
  if (nextParentId !== currentParentId) {
    payload.parent_id = nextParentId
  }

  if (!idsEqual(form.artist_ids, original.artists.map(artist => artist.id))) {
    payload.artist_ids = form.artist_ids.map(Number)
  }

  if (!idsEqual(form.tag_ids, original.tags.map(tag => tag.id))) {
    payload.tags = form.tag_ids.map(Number)
  }

  const originalDiscs = original.discs.map(disc => ({ id: disc.id, name: disc.name }))
  const nextDiscs = form.discs.map(disc => ({ id: disc.id, name: disc.name.trim() || `CD ${disc.number}` }))
  if (!discsEqual(originalDiscs, nextDiscs)) {
    payload.discs = nextDiscs.map(disc => ({
      id: Number(disc.id),
      name: disc.name
    }))
  }

  if (form.image_file) {
    payload.image_file = form.image_file
  }

  return payload
}

export function hasAlbumWritePatch (payload: IAlbumWriteDto): boolean {
  return Object.keys(payload).length > 0
}
