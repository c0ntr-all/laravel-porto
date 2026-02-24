export interface IRelationshipItem {
  id: string,
  type: string
}
export interface IHasOneRelationship {
  data: IRelationshipItem
}
export interface IHasManyRelationship {
  data: IRelationshipItem[],
  meta?: {
    count: number
  }
}
export interface IJsonApiResource {
  id: string
  type: string
  attributes?: Record<string, any>
  relationships?: Record<string, {
    data: IRelationshipItem | IRelationshipItem[] | null
  }>
}

export interface IJsonApiResponse<T = IJsonApiResource> {
  data: T | T[];
  included?: IJsonApiResource[];
  meta?: {
    count?: number,
    message?: string
    correlation_uuid?: string
  }
}

export type ApiError = import('axios').AxiosError<{ message: string }>
