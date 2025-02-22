import { Notify } from 'quasar'
import { AxiosError } from 'axios'

interface IRelationshipData {
  type: string
  id: string
  attributes: {
    [key: string]: any
  }
}
interface IRelationshipValue {
  data: IRelationshipData[]
}
interface IRelationshipItem {
  [key: string]: IRelationshipValue
}
interface IncludedItem {
  type: string
  id: string
  attributes: {
    [key: string]: any
  }
  relationships?: IRelationshipItem
}
interface IProcessItem {
  id: string
  [key: string]: any
  relationships: IRelationshipItem | undefined
}
interface IResolvedCollection<T> {
  data: T[]
  meta?: Record<string, any>
}
interface IResolvedItem<T> {
  data: T
  meta?: Record<string, any>
}

const processRelation = (mainRelData, included) => {
  const includedItem = included.find((include: IncludedItem) => {
    return include.type === mainRelData.type && include.id === mainRelData.id
  }) as IncludedItem

  const fullRelation = {
    id: includedItem.id,
    ...includedItem.attributes
  }

  if (includedItem.relationships) {
    for (const relName in includedItem.relationships) {
      const relData = includedItem.relationships[relName]

      if (!fullRelation.relationships) {
        fullRelation.relationships = {}
      }

      fullRelation.relationships[relName] = processRawRelation(relData, included)
    }
  }

  return fullRelation
}

const processRawRelation = (mainRelData, included) => {
  if (Array.isArray(mainRelData.data) ) {
    if (mainRelData.data.length > 0) {
      mainRelData.data = mainRelData.data.map((relItem: IRelationshipItem) => {
        return processRelation(relItem, included)
      })

      return mainRelData
    } else {
      return mainRelData
    }
  } else {
    return {
      data: processRelation(mainRelData.data, included)
    }
  }
}

export function normalizeApiResponse(responseData) {
  responseData.data = responseData.data.map((item) => {
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
