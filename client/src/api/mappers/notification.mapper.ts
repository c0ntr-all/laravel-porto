import { IJsonApiResponse } from 'src/types'
import {
  INotification,
  INotificationCreatedPayload,
  INotificationListMeta,
  INotificationListResult
} from 'src/types/notification'
import { mapResponse } from 'src/utils/jsonApiMapper'

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null && !Array.isArray(value)
}

function asString(value: unknown, fallback = ''): string {
  return typeof value === 'string' ? value : fallback
}

function asNumber(value: unknown, fallback: number): number {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}

export function emptyNotificationListMeta(): INotificationListMeta {
  return {
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0
  }
}

export function mapNotification(raw: Record<string, unknown>): INotification {
  const data = isRecord(raw.data) ? raw.data : {}

  return {
    id: String(raw.id ?? ''),
    type: asString(raw.type, 'system'),
    title: asString(raw.title),
    body: asString(raw.body),
    data,
    is_read: Boolean(raw.is_read),
    read_at: raw.read_at ? asString(raw.read_at) : null,
    created_at: asString(raw.created_at)
  }
}

export function mapNotificationList(payload: unknown): INotificationListResult {
  if (!isRecord(payload)) {
    return { items: [], meta: emptyNotificationListMeta() }
  }

  const response = payload as IJsonApiResponse
  const items = mapResponse(response)
    .map(item => mapNotification(item))
    .filter(item => item.id)

  const meta = isRecord(response.meta) ? response.meta : {}
  const pagination = isRecord(meta.pagination) ? meta.pagination : meta

  return {
    items,
    meta: {
      current_page: asNumber(pagination.current_page ?? pagination.currentPage, 1),
      last_page: asNumber(
        pagination.last_page ?? pagination.total_pages ?? pagination.totalPages,
        1
      ),
      per_page: asNumber(pagination.per_page ?? pagination.perPage, 20),
      total: asNumber(pagination.total, items.length)
    }
  }
}

export function mapRealtimeNotification(payload: INotificationCreatedPayload): INotification {
  return mapNotification({
    id: payload.id,
    type: payload.type,
    title: payload.title,
    body: payload.body,
    data: payload.data ?? {},
    is_read: payload.is_read,
    read_at: payload.read_at,
    created_at: payload.created_at
  })
}

export function previewNotificationBody(body: string, limit: number): string {
  const normalized = body.replace(/\s+/g, ' ').trim()
  if (normalized.length <= limit) {
    return normalized
  }

  return `${normalized.slice(0, limit).trimEnd()}…`
}
