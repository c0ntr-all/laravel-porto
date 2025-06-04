export interface IRelationshipData {
  type: string
  id: string
}

export interface IIncludedItem {
  type: string
  id: string
  attributes: Record<string, any>
  relationships?: any
}
