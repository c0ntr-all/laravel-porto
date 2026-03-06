import { IJsonApiResource, IJsonApiResponse } from 'src/types/jsonapi'

export interface ICommentFields {
  commentable_id: string
  commentable_type: string
  content: string
  created_at: string
}
export interface IComment extends ICommentFields {
  id: string
  userId: string
}
export interface ICommentCreatePayload {
  commentable_id: string
  commentable_type: string
  content: string
}
export interface ICommentResource extends IJsonApiResource {
  attributes: ICommentFields
}
export interface ICommentResponse extends IJsonApiResponse<ICommentResource> {
  data: ICommentResource
}
export interface ICommentGetResponse extends ICommentResponse {
  data: ICommentResource[]
}
export interface ICommentCreateResponse extends ICommentResponse {}
export interface ICommentUpdateResponse extends ICommentResponse {}
