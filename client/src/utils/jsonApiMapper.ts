import { IJsonApiResource, IJsonApiResponse, IRelationshipItem } from 'src/types'

export type MappedEntity = {
  id: string
  [key: string]: any
}

export function mapEntity(
  res: IJsonApiResource,
  included?: IJsonApiResource[]
): MappedEntity {
  const entity: MappedEntity = {
    id: res.id,
    ...res.attributes
  }

  if (res.relationships && included) {
    for (const [key, rel] of Object.entries(res.relationships)) {
      if (!rel.data) {
        entity[key] = null
        continue
      }

      const rels = Array.isArray(rel.data) ? rel.data : [rel.data]

      entity[key] = rels
        .map((r: IRelationshipItem) => included.find((i: IJsonApiResource) => i.type === r.type && i.id === r.id))
        .filter(Boolean)
        .map((r: IJsonApiResource | undefined) => mapEntity(r!, included))

      if (!Array.isArray(rel.data)) {
        entity[key] = entity[key][0] ?? null
      }
    }
  }

  return entity
}

export function mapResponse(
  response: IJsonApiResponse
): Record<string, any>[] {
  const arr: IJsonApiResource[] = Array.isArray(response.data)
    ? response.data
    : [response.data]

  return arr.map((res: IJsonApiResource) =>
    mapEntity(res, response.included)
  )
}
