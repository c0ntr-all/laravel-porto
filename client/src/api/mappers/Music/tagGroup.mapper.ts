import { IJsonApiResponse, IMusicTagGroup } from 'src/types'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { asRecord } from 'src/api/mappers/Music/helpers'

export function normalizeTagGroup(raw: Record<string, unknown>): IMusicTagGroup {
  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    slug: String(raw.slug ?? ''),
    description: raw.description == null ? null : String(raw.description),
    is_system: Boolean(raw.is_system),
    is_active: raw.is_active == null ? true : Boolean(raw.is_active),
    display_order: Number(raw.display_order ?? 0)
  }
}

export function normalizeTagGroupOptional(value: unknown): IMusicTagGroup | null {
  const raw = asRecord(value)

  return raw ? normalizeTagGroup(raw) : null
}

export function mapTagGroupResponse(response: IJsonApiResponse): IMusicTagGroup {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Tag group not found')
  }

  return normalizeTagGroup(raw)
}

export function mapTagGroupsResponse(response: IJsonApiResponse): IMusicTagGroup[] {
  return mapResponse(response).map(normalizeTagGroup)
}
