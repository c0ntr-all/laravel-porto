import { IAlbumType } from 'src/types'

export function normalizeAlbumType(raw: unknown): IAlbumType | null {
  if (!raw || typeof raw !== 'object') {
    return null
  }

  const record = raw as Record<string, unknown>
  const name = String(record.name ?? record.slug ?? '')

  if (!record.id && !name) {
    return null
  }

  return {
    id: String(record.id ?? ''),
    name,
    slug: String(record.slug ?? name),
    label: String(record.label ?? formatAlbumTypeLabel(name))
  }
}

export function formatAlbumTypeLabel(value?: string | null): string {
  if (!value) {
    return ''
  }

  return value
    .replace(/[-_]+/g, ' ')
    .replace(/\b\w/g, char => char.toUpperCase())
}
