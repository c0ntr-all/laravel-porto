import { isObject } from 'radash'

export function checkRelationExists(object: any, relation: string) {
  const relationObject = object[relation]
  if (relationObject) {
    return relationObject?.data?.length || isObject(relationObject?.data)
  }
}
