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
    count?: number
    message?: string
    correlation_uuid?: string
    current_page?: number
    last_page?: number
    total?: number
    per_page?: number
    has_more?: boolean
    next_cursor?: string | null
    prev_cursor?: string | null
    next_page_url?: string | null
    prev_page_url?: string | null
    cursor?: {
      current?: string | null
      prev?: string | null
      next?: string | null
      count?: number
    }
  }
  links?: {
    first?: string | null
    last?: string | null
    prev?: string | null
    next?: string | null
    self?: string | null
  }
}

export type ApiError = import('axios').AxiosError<{ message: string }>

export type IFilterValue = string | number | boolean | Array<string | number> | null | undefined

export interface IFilter {
  [key: string]: IFilterValue
}
