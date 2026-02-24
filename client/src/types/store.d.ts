export interface StoreEntity<T> {
  byId: Record<string, T>,
  allIds: string[],
  total?: number
}
