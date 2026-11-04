import { IJsonApiResponse, INote, INoteLink } from 'src/types'
import { mapResponse } from 'src/utils/jsonApiMapper'

function asString (value: unknown): string | null {
  return typeof value === 'string' ? value : null
}

function asBoolean (value: unknown): boolean {
  if (typeof value === 'boolean') return value
  if (typeof value === 'number') return value === 1
  if (typeof value === 'string') return value === '1' || value === 'true'
  return false
}

function asCount (value: unknown): number {
  const parsed = typeof value === 'number' ? value : Number(value)
  return Number.isFinite(parsed) ? parsed : 0
}

function asLinks (value: unknown): INoteLink[] {
  if (!Array.isArray(value)) return []

  return value.map((item) => {
    const row = item && typeof item === 'object' ? item as Record<string, unknown> : {}

    return {
      id: asString(row.id) ?? '',
      name: asString(row.name),
      label: asString(row.label) ?? ''
    }
  })
}

function asLabels (value: unknown): string[] {
  if (!Array.isArray(value)) return []

  return value.filter((item): item is string => typeof item === 'string')
}

export function normalizeNote (raw: Record<string, unknown>): INote {
  const pinPosition = raw.pin_position
  const parsedPin = typeof pinPosition === 'number' ? pinPosition : Number(pinPosition)

  return {
    id: asString(raw.id) ?? '',
    name: asString(raw.name) ?? '',
    content: asString(raw.content),
    content_raw: asString(raw.content_raw),
    excerpt: asString(raw.excerpt) ?? '',
    parent_id: asString(raw.parent_id),
    children_count: asCount(raw.children_count),
    is_pinned: asBoolean(raw.is_pinned),
    pin_position: pinPosition == null || !Number.isFinite(parsedPin) ? null : parsedPin,
    wiki_links: asLinks(raw.wiki_links),
    backlinks: asLinks(raw.backlinks),
    unresolved_links: asLabels(raw.unresolved_links),
    ambiguous_links: asLabels(raw.ambiguous_links),
    created_at: asString(raw.created_at),
    // Version token Y-m-d H:i:s.u. Do not parse or reformat it.
    updated_at: asString(raw.updated_at) ?? ''
  }
}

export function mapNoteResponse (response: IJsonApiResponse): INote {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Заметка не найдена')
  }

  return normalizeNote(raw)
}

export function mapNotesResponse (response: IJsonApiResponse): INote[] {
  return mapResponse(response).map(item => normalizeNote(item))
}
