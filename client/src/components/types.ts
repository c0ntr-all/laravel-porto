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

export interface IAction {
  name: string
  label: string
  icon: string
  color?: string
  is_active: boolean
  func(): void
}
