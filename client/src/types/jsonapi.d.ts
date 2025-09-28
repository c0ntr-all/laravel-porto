export interface IResponseRelationshipItem {
  id: string,
  type: string
}
export interface IResponseHasOneRelationship {
  data: IResponseRelationshipItem
}
export interface IResponseHasManyRelationship {
  data: IResponseRelationshipItem[],
  meta?: {
    count: number
  }
}
