import { IJsonApiResponse } from 'src/types'
import { IGame } from 'src/types/Game'
import { mapResponse } from 'src/utils/jsonApiMapper'

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

export function normalizeGame(raw: Record<string, unknown>): IGame {
  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    description: toNullableString(raw.description),
    img: toNullableString(raw.img),
    air_date: toNullableString(raw.air_date),
    developer: toNullableString(raw.developer),
    publisher: toNullableString(raw.publisher),
    steam_id: toNullableNumber(raw.steam_id),
    created_at: toNullableString(raw.created_at),
    updated_at: toNullableString(raw.updated_at)
  }
}

export function mapGamesResponse(response: IJsonApiResponse): IGame[] {
  return mapResponse(response).map(normalizeGame)
}

export function mapGameResponse(response: IJsonApiResponse): IGame {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Game not found')
  }

  return normalizeGame(raw)
}
