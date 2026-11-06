import { IJsonApiResource } from 'src/types'

export {
  handleApiError,
  handleApiSuccess,
  isRequestCanceled
} from 'src/utils/apiNotify'
export {
  buildFilterForUrl,
  extractCursorFromLink,
  extractCursorFromResponse,
  hasMoreFromResponse
} from 'src/utils/apiQuery'
export { patchEntity, upsertEntity } from 'src/utils/entityStore'

interface IIncluded {
  type: string
  id: string
  attributes: {
    [key: string]: any
  }
}
interface IRelationshipValue {
  data: IIncluded[]
}
interface IRelationshipItem {
  [key: string]: IRelationshipValue
}
interface IProcessItem {
  id: string
  relationships?: IRelationshipItem | undefined
  [key: string]: any
}
interface IncludedItem {
  type: string
  id: string
  attributes: {
    [key: string]: any
  }
  relationships?: IRelationshipItem
}
interface IResolvedCollection<T> {
  data: T[]
  meta?: Record<string, any>
}
interface IResolvedItem<T> {
  data: T
  meta?: Record<string, any>
}
export function getIncluded<T>(
  chain: string,
  relationships: any,
  included: IncludedItem[],
  enableWrapping: boolean = true
): IResolvedCollection<T> | IResolvedItem<T> | T[] | T {
  const chainArray = chain.split('.')
  const initRelationName = chainArray[0]
  const firstLevelRelations = relationships[initRelationName]

  if (!firstLevelRelations || !firstLevelRelations.data) {
    return enableWrapping ? { data: [] } : []
    // weird javascript typeof result "object" on Array with objects
  } else if (!Array.isArray(firstLevelRelations.data) && typeof firstLevelRelations.data === 'object') {
    const groupedItem = processItem(initRelationName, firstLevelRelations.data, included)

    return enableWrapping ? { data: groupedItem as T } : groupedItem as T
  }

  const result = firstLevelRelations.data.map((item: IRelationshipItem) => {
    const groupedItem = processItem(initRelationName, item, included)

    if (chainArray.length > 1) {
      let remainingRelations = chainArray.slice(1).join('.')
      let nextRelationName = chainArray[1]
      if (groupedItem.relationships) {
        if (remainingRelations === '*') {
          remainingRelations = chainArray.join('.')
          nextRelationName = chainArray[0]
        }

        const nestedRelationships = getIncluded(remainingRelations, groupedItem.relationships, included, enableWrapping)

        if (enableWrapping) {
          groupedItem.relationships[nextRelationName] = nestedRelationships as IRelationshipValue
        } else {
          groupedItem[nextRelationName] = nestedRelationships
        }
      }
    }

    return groupedItem
  })

  if (enableWrapping) {
    return {
      ...relationships[initRelationName],
      data: result
    }
  } else {
    return result
  }
}

const processItem = (relationName: string, relationItem: IRelationshipItem, included: IncludedItem[]): IProcessItem => {
  const includedItem = included.find((include: IncludedItem) => {
    return include.type === relationName && include.id === (relationItem as any).id
  }) as IncludedItem

  return {
    id: includedItem.id,
    ...includedItem.attributes,
    relationships: includedItem.relationships
  }
}

function indexIncluded(included: IJsonApiResource[] = []) {
  const map = new Map<string, IJsonApiResource>()

  for (const item of included) {
    map.set(`${item.type}:${item.id}`, item)
  }

  return map
}

export function normalizeEntity<T extends { id: string }>(
  entity: IJsonApiResource,
  included: IJsonApiResource[] = []
): { entity: T; related: Record<string, T[]> } {
  const includedMap = new Map(
    included.map(i => [`${i.type}:${i.id}`, i])
  )

  const related: Record<string, T[]> = {}
  const visited = new Set<string>()

  function normalizeRecursive(resource: IJsonApiResource): T {
    const key = `${resource.type}:${resource.id}`
    if (visited.has(key)) {
      return {
        id: resource.id,
        ...(resource.attributes ?? {})
      } as T
    }

    visited.add(key)

    const normalized: any = {
      id: resource.id,
      type: resource.type,
      ...(resource.attributes ?? {})
    }

    if (resource.relationships) {
      for (const relName in resource.relationships) {
        const relData = resource.relationships[relName].data

        if (!relData) {
          normalized[`${relName}Ids`] = []
          continue
        }

        if (Array.isArray(relData)) {
          normalized[`${relName}Ids`] = relData.map(r => r.id)

          for (const rel of relData) {
            const includedItem = includedMap.get(
              `${rel.type}:${rel.id}`
            )

            if (!includedItem) continue

            const normalizedRelated = normalizeRecursive(
              includedItem
            )

            if (!related[relName]) {
              related[relName] = []
            }

            // защита от дублей
            if (!related[relName].some(i => i.id === normalizedRelated.id)) {
              related[relName].push(normalizedRelated)
            }
          }
        } else {
          normalized[`${relName}Id`] = relData.id

          const includedItem = includedMap.get(
            `${relData.type}:${relData.id}`
          )

          if (!includedItem) continue

          const normalizedRelated = normalizeRecursive(
            includedItem
          )

          if (!related[relName]) {
            related[relName] = []
          }

          related[relName].push(normalizedRelated)
        }
      }
    }

    return normalized as T
  }

  const normalizedEntity = normalizeRecursive(entity)

  return {
    entity: normalizedEntity,
    related
  }
}

export function normalizeEntityCollection(
  data: IJsonApiResource[],
  included: IJsonApiResource[] = []
) {
  const includedIndex = indexIncluded(included)

  const entities: Record<string, Record<string, any>> = {}

  function ensureBucket(type: string) {
    if (!entities[type]) {
      entities[type] = {}
    }
  }

  function normalizeResource(resource: IJsonApiResource) {
    const id = resource.id
    const type = resource.type

    ensureBucket(type)

    const normalized: any = {
      id,
      type,
      ...(resource.attributes ?? {})
    }

    if (resource.relationships) {
      for (const relName in resource.relationships) {
        const relData = resource.relationships[relName].data

        if (!relData) {
          normalized[`${relName}Ids`] = []
          continue
        }

        if (Array.isArray(relData)) {
          const ids: string[] = []

          for (const r of relData) {
            ids.push(r.id)

            const includedItem = includedIndex.get(`${r.type}:${r.id}`)
            if (includedItem) {
              normalizeResource(includedItem)
            }
          }

          normalized[`${relName}Ids`] = ids
        } else {
          normalized[`${relName}Id`] = relData.id

          const includedItem = includedIndex.get(`${relData.type}:${relData.id}`)
          if (includedItem) {
            normalizeResource(includedItem)
          }
        }
      }
    }

    entities[type][id] = normalized
  }

  for (const item of data) {
    normalizeResource(item)
  }

  return entities
}
