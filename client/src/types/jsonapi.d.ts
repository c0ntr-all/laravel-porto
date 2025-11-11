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
  id: string;
  type: string;
  attributes: Record<string, any>;
  relationships?: Record<
    string,
    {
      data: { id: string; type: string } | { id: string; type: string }[] | null;
    }
  >;
}

export interface IJsonApiResponse {
  data: IJsonApiResource | IJsonApiResource[];
  included?: IJsonApiResource[];
  meta?: {
    count?: number,
    correlation_uuid?: string
  }
}
