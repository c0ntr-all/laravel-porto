import { isObject } from 'radash'

export function checkRelationExists(object: any, relation: string) {
  const relationObject = object[relation]
  if (relationObject) {
    return relationObject?.data?.length || isObject(relationObject?.data)
  }
}

/**
 * Обновляет все поля из объекта updates в целевом объекте target
 *
 * @param target Целевой объект, который нужно обновить
 * @param updates Объект с новыми значениями полей
 * @returns Обновленный целевой объект (такой же экземпляр)
 */
export function updateObject<T extends Record<string, any>>(
  target: T,
  updates: Partial<T>
): T {
  for (const key in updates) {
    if (Object.prototype.hasOwnProperty.call(updates, key)) {
      target[key] = updates[key] as T[Extract<keyof T, string>]
    }
  }
  return target
}
