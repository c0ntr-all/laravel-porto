import { Notify } from 'quasar'
import { AxiosError } from 'axios'

interface RelationshipItem {
  type: string
  id: string
  meta?: Record<string, any>
}

interface IncludedItem {
  type: string
  id: string
  attributes: Record<string, any>
  relationships?: any
}

interface ResolvedResource<T> {
  data: T[]
  meta?: Record<string, any>
}

export function getIncluded<T>(
  chain: string,
  relationships: any,
  included: IncludedItem[],
  enableWrapping: boolean = true
): ResolvedResource<T> | T[] {
  const chainArray = chain.split('.')
  const initRelationName = chainArray[0]
  const firstLevelRelations = relationships[initRelationName]

  if (!firstLevelRelations || !firstLevelRelations.data) {
    return enableWrapping ? { data: [] } : []
  }

  const result = firstLevelRelations.data.map((item: RelationshipItem) => {
    const includedItem = included.find((include: IncludedItem) => include.type === initRelationName && include.id === item.id)

    if (!includedItem) {
      return []
    }

    const groupedItem: Record<string, any> = {
      id: includedItem.id,
      ...includedItem.attributes
    }

    if (chainArray.length > 1) {
      let remainingRelations = chainArray.slice(1).join('.')
      let nextRelationName = chainArray[1]
      if (includedItem.relationships) {
        if (remainingRelations === '*') {
          remainingRelations = chainArray.join('.')
          nextRelationName = chainArray[0]
        }

        const nestedRelationships = getIncluded(remainingRelations, includedItem.relationships, included, enableWrapping)

        if (enableWrapping) {
          groupedItem.relationships = {}
          groupedItem.relationships[nextRelationName] = nestedRelationships
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

export function handleApiError(error: AxiosError) {
  const message = (error.response?.data as { message?: string })?.message || 'Error!'

  Notify.create({
    type: 'negative',
    message
  })
}

export function handleApiSuccess(response: any) {
  const message = response.data.meta.message || 'The action was done!'

  Notify.create({
    type: 'positive',
    message
  })
}
