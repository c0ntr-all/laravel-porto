import { IJsonApiResponse, IMusicTag } from 'src/types'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { asRecords } from 'src/api/mappers/Music/helpers'
import { normalizeTagGroupOptional } from 'src/api/mappers/Music/tagGroup.mapper'

export function normalizeMusicTag(raw: Record<string, unknown>): IMusicTag {
  const description = raw.description ?? raw.content

  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    slug: String(raw.slug ?? ''),
    description: description == null ? null : String(description),
    is_active: raw.is_active == null ? true : Boolean(raw.is_active),
    parent_id: raw.parent_id == null ? null : String(raw.parent_id),
    group_id: raw.group_id == null ? null : String(raw.group_id),
    group: normalizeTagGroupOptional(raw.group),
    tags: asRecords(raw.tags).map(normalizeMusicTag),
    content: description == null ? null : String(description),
    is_base: Boolean(raw.is_base)
  }
}

export function normalizeMusicTags(value: unknown): IMusicTag[] {
  return asRecords(value).map(normalizeMusicTag)
}

export function mapTagResponse(response: IJsonApiResponse): IMusicTag {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Tag not found')
  }

  return normalizeMusicTag(raw)
}

export function mapTagsResponse(response: IJsonApiResponse): IMusicTag[] {
  return mapResponse(response).map(normalizeMusicTag)
}
