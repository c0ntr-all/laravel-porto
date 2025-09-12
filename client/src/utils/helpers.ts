import { isObject } from 'radash'

export function checkRelationExists(object: object, relation: string) {
  const relationObject = object?.relationships[relation]
  if (relationObject) {
    return relationObject?.data?.length || isObject(relationObject?.data)
  }
}
