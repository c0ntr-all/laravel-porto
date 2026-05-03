import { IJsonApiResource, IJsonApiResponse } from 'src/types'

/**
 * Only backend fields
 */
export interface IPeriodFields {
  title: string
  description: string | null
  color: string | null
  icon: boolean
}
export interface IPeriodCreatePayload {
  title: string
  description?: string
  start_post_id: string
  end_post_id?: string
  color: string
  icon?: string
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
