import { IMusicTag } from 'src/types'
import { asRecords } from 'src/api/mappers/Music/helpers'

export function normalizeMusicTag(raw: Record<string, unknown>): IMusicTag {
  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    content: raw.content == null ? null : String(raw.content),
    is_base: Boolean(raw.is_base),
    parent_id: raw.parent_id == null ? null : String(raw.parent_id)
  }
}

export function normalizeMusicTags(value: unknown): IMusicTag[] {
  return asRecords(value).map(normalizeMusicTag)
}
