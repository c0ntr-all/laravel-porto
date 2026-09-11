import { IJsonApiResponse } from 'src/types'
import { IMovie, IMovieCountry, IMovieGenre } from 'src/types/Movie'
import { MovieTypeEnum } from 'src/enums/Movie/MovieTypeEnum'
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
