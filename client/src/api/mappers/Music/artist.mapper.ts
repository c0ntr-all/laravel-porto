import { IJsonApiResponse, IArtist } from 'src/types'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { normalizeMusicTags } from 'src/api/mappers/Music/tag.mapper'

export function normalizeArtist(raw: Record<string, unknown>): IArtist {
  return {
    id: String(raw.id),
    name: String(raw.name ?? ''),
    description: raw.description == null ? null : String(raw.description),
    image: String(raw.image ?? ''),
    created_at: raw.created_at ? String(raw.created_at) : undefined,
    tags: normalizeMusicTags(raw.tags)
  }
}

export function mapArtistResponse(response: IJsonApiResponse): IArtist {
  const [raw] = mapResponse(response)

  if (!raw) {
    throw new Error('Artist not found')
  }

  return normalizeArtist(raw)
}
