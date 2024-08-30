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
  data: T[] | any[]
  meta?: Record<string, any>
}

export function getIncluded<T>(
  chain: string,
  relationships: any,
  included: IncludedItem[]
): ResolvedResource<T> {
  const chainArray = chain.split('.')
  const initRelationName = chainArray[0]
  const firstLevelRelations = relationships[initRelationName]

  if (!firstLevelRelations || !firstLevelRelations.data) {
    return {
      data: []
    }
  }

  return {
    ...relationships[initRelationName],
    data: firstLevelRelations.data.map((item: RelationshipItem) => {
      const includedItem = included.find((include: IncludedItem) => include.type === initRelationName && include.id === item.id)

      if (!includedItem) {
        return []
      }

      const groupedItem: Record<string, any> = {
        id: includedItem.id,
        ...includedItem.attributes
      }

      if (chainArray.length > 1) {
        const remainingRelations = chainArray.slice(1).join('.')
        if (includedItem.relationships) {
          groupedItem.relationships = {}
          groupedItem.relationships[chainArray[1]] = getIncluded(remainingRelations, includedItem.relationships, included)
        }
      }

      return groupedItem
    })
  }
}

export function handleApiError(error: AxiosError) {
  const message = (error.response?.data as { message?: string })?.message || 'Error!'

  Notify.create({
    type: 'negative',
    message
  })
}
