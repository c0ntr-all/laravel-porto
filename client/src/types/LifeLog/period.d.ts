import { IJsonApiResource, IJsonApiResponse, IUser } from 'src/types'

export interface IPeriod {
  type: string
  id: string
  title?: string
  description?: string
  color: string
  icon: string | null
  start_date: string
  end_date: string | null
  start_post_id: string
  end_post_id: string | null
  created_at: string
  user: IUser
}
export interface IPeriodModel {
  title: string,
  description?: string,
  start_post_id: string,
  end_post_id: string | null,
  color: string,
  icon?: string | null
}
/**
 * Only backend fields
 */
export interface IPeriodFields {
  title: string
  description: string | null
  color: string | null
  icon: boolean
}
export interface IPeriodUpdatePayload extends Partial<IPeriodFields> {}
export interface IPeriodResource extends IJsonApiResource {
  attributes: IPeriodFields
}
export interface IPeriodResponse extends IJsonApiResponse<IPeriodResource> {
  data: IPeriodResource
}
export interface IPeriodGetResponse extends IPeriodResponse {}
export interface IPeriodCreateResponse extends IPeriodResponse {}
export interface IPeriodUpdateResponse extends IPeriodResponse {}
export interface IPeriodDeleteResponse extends IPeriodResponse {}
