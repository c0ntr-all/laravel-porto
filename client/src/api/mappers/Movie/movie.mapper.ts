import { IJsonApiResponse } from 'src/types'
import { IMovie, IMovieCountry, IMovieGenre, IMovieImport } from 'src/types/Movie'
import { MovieTypeEnum } from 'src/enums/Movie/MovieTypeEnum'
import { MovieImportStatusEnum } from 'src/enums/Movie/MovieImportStatusEnum'
import { mapResponse } from 'src/utils/jsonApiMapper'

function asRecords(value: unknown): Record<string, unknown>[] {
  if (!value) {
    return []
  }

  if (Array.isArray(value)) {
    return value.filter((item): item is Record<string, unknown> => (
      Boolean(item) && typeof item === 'object'
    ))
  }

  if (typeof value === 'object') {
    return [value as Record<string, unknown>]
  }

  return []
}

function toNullableNumber(value: unknown): number | null {
  if (value === null || value === undefined || value === '') {
    return null
  }

  const parsed = Number(value)

  return Number.isFinite(parsed) ? parsed : null
}

function toNullableString(value: unknown): string | null {
  if (value === null || value === undefined || value === '') {
    return null
  }

  return String(value)
}

function normalizeMovieType(value: unknown): MovieTypeEnum {
  if (value === MovieTypeEnum.TV_SERIES || value === MovieTypeEnum.SHOW) {
    return value
  }

  return MovieTypeEnum.MOVIE
}

export function normalizeMovieGenre(raw: Record<string, unknown>): IMovieGenre {
  return {
    id: String(raw.id),
    kp_id: toNullableNumber(raw.kp_id),
    name: String(raw.name ?? ''),
    slug: String(raw.slug ?? '')
  }
}

export function normalizeMovieCountry(raw: Record<string, unknown>): IMovieCountry {
  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    description: toNullableString(raw.description),
    image: toNullableString(raw.image)
  }
}

export function normalizeMovie(raw: Record<string, unknown>): IMovie {
  return {
    id: String(raw.id),
    kp_id: Number(raw.kp_id ?? 0),
    title: String(raw.title ?? ''),
    description: toNullableString(raw.description),
    short_description: toNullableString(raw.short_description),
    year: Number(raw.year ?? 0),
    type: normalizeMovieType(raw.type),
    cover: toNullableString(raw.cover),
    kp_rating: toNullableNumber(raw.kp_rating),
    kp_img: toNullableString(raw.kp_img),
    created_at: toNullableString(raw.created_at),
    updated_at: toNullableString(raw.updated_at),
    genres: asRecords(raw.genres).map(normalizeMovieGenre),
    countries: asRecords(raw.countries).map(normalizeMovieCountry)
  }
}

export function mapMoviesResponse(response: IJsonApiResponse): IMovie[] {
  return mapResponse(response).map(normalizeMovie)
}

export function mapMovieResponse(response: IJsonApiResponse): IMovie {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Movie not found')
  }

  return normalizeMovie(raw)
}

export function moviePosterUrl(movie: Pick<IMovie, 'cover' | 'kp_img'>): string | null {
  return movie.cover || movie.kp_img || null
}

export function mapMovieGenresResponse(response: IJsonApiResponse): IMovieGenre[] {
  return mapResponse(response).map(normalizeMovieGenre)
}

export function mapMovieGenreResponse(response: IJsonApiResponse): IMovieGenre {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Genre not found')
  }

  return normalizeMovieGenre(raw)
}

function asRecord(value: unknown): Record<string, unknown> | null {
  if (!value || typeof value !== 'object' || Array.isArray(value)) {
    return null
  }

  return value as Record<string, unknown>
}

function asBoolean(value: unknown): boolean | null {
  if (value === null || value === undefined || value === '') {
    return null
  }

  return Boolean(value)
}

function normalizeMovieImportStatus(value: unknown): MovieImportStatusEnum {
  if (value === MovieImportStatusEnum.COMPLETED || value === MovieImportStatusEnum.FAILED) {
    return value
  }

  return MovieImportStatusEnum.PENDING
}

export function normalizeMovieImport(raw: Record<string, unknown>): IMovieImport {
  const movieRaw = asRecord(raw.movie)

  return {
    id: String(raw.id),
    kp_id: Number(raw.kp_id ?? 0),
    movie_id: toNullableNumber(raw.movie_id),
    source_url: String(raw.source_url ?? ''),
    status: normalizeMovieImportStatus(raw.status),
    http_status: toNullableNumber(raw.http_status),
    was_created: asBoolean(raw.was_created),
    parsed_payload: asRecord(raw.parsed_payload),
    meta: asRecord(raw.meta),
    error_message: toNullableString(raw.error_message),
    started_at: toNullableString(raw.started_at),
    finished_at: toNullableString(raw.finished_at),
    duration_ms: toNullableNumber(raw.duration_ms),
    created_at: toNullableString(raw.created_at),
    movie: movieRaw ? normalizeMovie(movieRaw) : null
  }
}

export function mapMovieImportsResponse(response: IJsonApiResponse): IMovieImport[] {
  return mapResponse(response).map(normalizeMovieImport)
}

export function mapMovieImportResponse(response: IJsonApiResponse): IMovieImport {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Import not found')
  }

  return normalizeMovieImport(raw)
}
