import { IJsonApiResponse } from 'src/types'
import { IGame, IGameImport } from 'src/types/Game'
import { GameImportStatusEnum } from 'src/enums/Game/GameImportStatusEnum'
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

function normalizeGameImportStatus(value: unknown): GameImportStatusEnum {
  if (value === GameImportStatusEnum.COMPLETED || value === GameImportStatusEnum.FAILED) {
    return value
  }

  return GameImportStatusEnum.PENDING
}

export function normalizeGameImport(raw: Record<string, unknown>): IGameImport {
  const gameRaw = asRecord(raw.game)

  return {
    id: String(raw.id),
    steam_id: Number(raw.steam_id ?? 0),
    game_id: toNullableNumber(raw.game_id),
    source_url: String(raw.source_url ?? ''),
    status: normalizeGameImportStatus(raw.status),
    http_status: toNullableNumber(raw.http_status),
    was_created: asBoolean(raw.was_created),
    parsed_payload: asRecord(raw.parsed_payload),
    meta: asRecord(raw.meta),
    error_message: toNullableString(raw.error_message),
    started_at: toNullableString(raw.started_at),
    finished_at: toNullableString(raw.finished_at),
    duration_ms: toNullableNumber(raw.duration_ms),
    created_at: toNullableString(raw.created_at),
    game: gameRaw ? normalizeGame(gameRaw) : null
  }
}

export function mapGameImportsResponse(response: IJsonApiResponse): IGameImport[] {
  return mapResponse(response).map(normalizeGameImport)
}

export function mapGameImportResponse(response: IJsonApiResponse): IGameImport {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Import not found')
  }

  return normalizeGameImport(raw)
}
