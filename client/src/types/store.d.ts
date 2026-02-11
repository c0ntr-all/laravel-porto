export interface StoreEntity<T> {
  byId: Record<number, T>,
  allIds: number[],
  total?: number
}
