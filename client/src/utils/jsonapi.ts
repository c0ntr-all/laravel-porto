import { Notify } from 'quasar'
import { AxiosError } from 'axios'

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

      if (!fullRelation.relationships) {
        fullRelation.relationships = {}
      }

      fullRelation.relationships[relName] = <IRelationshipValue>processRawRelation(relData, included)
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
  responseData.data = responseData.data.map((item: IRawElement) => {
    if (item.relationships) {
      for (const relName in item.relationships) {
        const relData = item.relationships[relName]

        item.relationships[relName] = processRawRelation(relData, responseData.included)
      }
    }

    return item
  })

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
  const message = (error.response?.data as { message?: string })?.message || 'Error!'

  Notify.create({
    type: 'negative',
    message
  })
}

export function handleApiSuccess(response: any) {
  const message = response.data?.meta?.message || 'The action was done!'

  Notify.create({
    type: 'positive',
    message
  })
}
