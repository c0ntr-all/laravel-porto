import { StoreEntity } from 'src/types/store'
import { updateObject } from 'src/utils/helpers'

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
