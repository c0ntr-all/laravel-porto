import { IJsonApiResource, IJsonApiResponse } from 'src/types/jsonapi'

export interface IUseCaseLogFields {
  loggable_id: string
  loggable_type: string
  event_type: string
  created_at: string
}
export interface IUseCaseLog extends IUseCaseLogFields {
  id: string
  userId: string
}
export interface IUseCaseLogListPayload {
  loggable_id: string
  loggable_type: string
}
export interface IUseCaseLogResource extends IJsonApiResource {
  attributes: IUseCaseLogFields
}
export interface IUseCaseLogResponse extends IJsonApiResponse<IUseCaseLogResource> {
  data: IUseCaseLogResource
}
export interface IUseCaseLogGetResponse extends IUseCaseLogResponse {
  data: IUseCaseLogResource[]
}
export interface IUseCaseLogCreateResponse extends IUseCaseLogResponse {}
export interface IUseCaseLogUpdateResponse extends IUseCaseLogResponse {}
