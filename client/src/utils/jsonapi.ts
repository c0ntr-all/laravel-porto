import { Notify } from 'quasar'
import { AxiosError } from 'axios'
import { StoreEntity } from 'src/types/store'
import {IJsonApiResource} from "src/types";
import {updateObject} from "src/utils/helpers";

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
interface IRawRelationItem {
  id: string
  type: string
}
interface IRawRelations {
  data: IRawRelationItem[] | IProcessItem[]
  meta?: any
}
interface IncludedItem {
  type: string
  id: string
  attributes: {
    [key: string]: any
  }
  relationships?: IRelationshipItem
}
interface IRawElement {
  id: string
  type: string
  attributes: {
    [key: string]: any
  }
  relationships?: any
}
interface IResolvedCollection<T> {
  data: T[]
  meta?: Record<string, any>
}
interface IResolvedItem<T> {
  data: T
  meta?: Record<string, any>
}
interface IResponse {
  data: IRawElement[]
  included?: any
  meta: any
}
// interface IItemResponse {
//   data: IRawElement
//   included?: any
//   meta: any
// }

const processRelation = (mainRelData: IRawRelationItem, included: IncludedItem[]) => {
  const includedItem = included.find((include: IncludedItem) => {
    return include.type === mainRelData.type && include.id === mainRelData.id
  }) as IncludedItem

  const fullRelation: IProcessItem = {
    id: includedItem.id,
    ...includedItem.attributes
  }

  if (includedItem.relationships) {
    for (const relName in includedItem.relationships) {
      const relData: IRawRelations = includedItem.relationships[relName]

      fullRelation[relName] = <IRelationshipValue>processRawRelation(relData, included)
    }
  }

  return fullRelation
}

const processRawRelation = (mainRelData: IRawRelations, included: IncludedItem[]) => {
  if (Array.isArray(mainRelData.data)) {
    if (mainRelData.data.length > 0) {
      if (!isIRawRelationItemArray(mainRelData.data)) {
        return mainRelData // Если данные уже IProcessItem[], просто возвращаем
      }
      mainRelData.data = mainRelData.data.map((relItem: IRawRelationItem) => {
        return processRelation(relItem, included)
      })

      return mainRelData
    } else {
      return mainRelData
    }
  } else {
    return {
      data: processRelation(mainRelData.data as IRawRelationItem, included)
    }
  }
}
// Type Guard
const isIRawRelationItemArray = (data: any[]): data is IRawRelationItem[] => {
  return data.every(item => 'type' in item && 'id' in item && !('relationships' in item))
}

export function normalizeApiResponse(responseData: IResponse) {
  // TODO: any to clear type
  responseData.data = responseData.data.map((item: any) => {
    if (item.relationships) {
      for (const relName in item.relationships) {
        const relData = item.relationships[relName]

        item[relName] = processRawRelation(relData, responseData.included)
      }

      delete item.relationships
    }

    return item
  })

  return responseData
}

export function normalizeApiItemResponse(responseData: any) {
  // TODO: any to clear type
  if (responseData.data.relationships) {
    for (const relName in responseData.data.relationships) {
      const relData = responseData.data.relationships[relName]

      responseData.data[relName] = processRawRelation(relData, responseData.included)
    }

    delete responseData.data.relationships
  }

  return responseData
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

export function handleApiError(error: AxiosError) {
  const message = (error.response?.data as { message?: string })?.message || error.message
  console.error(error.message)

  Notify.create({
    type: 'negative',
    message
  })
}

export function handleApiSuccess(response: any) {
  const message = response.meta?.message || 'The action was done!'

  Notify.create({
    type: 'positive',
    message
  })
}

/**
 * Строит URL с фильтрами в формате, совместимом со Spatie Query Builder (например: ?filter[tags]=1,2,3).
 *
 * @param {Record<string, any>} filters - Объект фильтров, где ключ — это имя фильтра, а значение — строка или массив значений.
 * @returns {string} Полный URL с корректно сформированными query-параметрами.
 *
 * @example
 * // Пример использования:
 * const url = buildFilterUrl('v1/posts', { tags: [1, 2, 3], author: 'John' })
 * // Результат: "filter[tags]=1,2,3&filter[author]=John"
 *
 * @remarks
 * - Массивы значений объединяются через запятую.
 * - Пустые, `null` и `undefined` значения игнорируются.
 * - Используется `URLSearchParams` для корректного экранирования параметров.
 */
export function buildFilterForUrl(filters: Record<string, any>): string {
  const params = new URLSearchParams()

  for (const [key, value] of Object.entries(filters)) {
    if (Array.isArray(value)) {
      params.append(`filter[${key}]`, value.join(','))
    } else if (value !== undefined && value !== null && value !== '') {
      params.append(`filter[${key}]`, String(value))
    }
  }

  return params.toString()
}

// NEW

function indexIncluded(included: IJsonApiResource[] = []) {
  const map = new Map<string, IJsonApiResource>()

  for (const item of included) {
    map.set(`${item.type}:${item.id}`, item)
  }

  return map
}

export function normalizeEntity<T extends { id: string }>(
  entity: JsonApiResource,
  included: JsonApiResource[] = []
): { entity: T; related: Record<string, T[]> } {
  const includedMap = new Map(
    included.map(i => [`${i.type}:${i.id}`, i])
  )

  const related: Record<string, T[]> = {}
  const visited = new Set<string>()

  function normalizeRecursive(resource: JsonApiResource): T {
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

        const relArray = Array.isArray(relData)
          ? relData
          : [relData]

        normalized[`${relName}Ids`] = relArray.map(r => r.id)

        for (const rel of relArray) {
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
          if (
            !related[relName].some(
              i => i.id === normalizedRelated.id
            )
          ) {
            related[relName].push(normalizedRelated)
          }
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
        const rel = resource.relationships[relName].data

        if (!rel) {
          normalized[`${relName}Ids`] = []
          continue
        }

        if (Array.isArray(rel)) {
          const ids: string[] = []

          for (const r of rel) {
            ids.push(r.id)

            const includedItem = includedIndex.get(`${r.type}:${r.id}`)
            if (includedItem) {
              normalizeResource(includedItem)
            }
          }

          normalized[`${relName}Ids`] = ids
        } else {
          normalized[`${relName}Id`] = rel.id

          const includedItem = includedIndex.get(`${rel.type}:${rel.id}`)
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

/**
 * Целиком обновляет entity в сторе по id
 */
export function upsertEntity<T extends { id: string }>(
  store: StoreEntity<T>,
  entity: T
) {
  if (!store.byId[entity.id]) {
    store.allIds.push(entity.id)
  }

  store.byId[entity.id] = entity
}

/**
 * Поточечно обновляет свойства entity в сторе по id
 */
export function patchEntity<T extends { id: string }>(
  store: StoreEntity<T>,
  entity: T
) {
  const object = store.byId[entity.id]
  const updated = updateObject(object, entity)

  upsertEntity(store, updated)
}
