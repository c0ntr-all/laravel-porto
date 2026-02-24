import { IJsonApiResource } from 'src/types/jsonapi'

export interface ICommentFields {
  content: string
  created_at: string
}
export interface IComment extends ICommentFields {
  id: string
  userIds: string[]
}
export interface ICommentCreatePayload {
  commentable_id: string,
  commentable_type: string,
  content: string
}
export interface ICommentResponse extends IJsonApiResource {
  attributes: ICommentFields
}
export interface ICommentCreateResponse extends ICommentResponse {}
export interface ICommentUpdateResponse extends ICommentResponse {}
