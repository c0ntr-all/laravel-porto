import { IJsonApiResource, IJsonApiResponse, IUser } from 'src/types'

export interface IPreset {
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
export interface IPresetModel {
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
export interface IPresetFields {
  title: string
  description: string | null
  color: string | null
  icon: boolean
}
export interface IPresetUpdatePayload extends Partial<IPresetFields> {}
export interface IPresetResource extends IJsonApiResource {
  attributes: IPresetFields
}
export interface IPresetResponse extends IJsonApiResponse<IPresetResource> {
  data: IPresetResource
}
export interface IPresetGetResponse extends IPresetResponse {}
export interface IPresetCreateResponse extends IPresetResponse {}
export interface IPresetUpdateResponse extends IPresetResponse {}
export interface IPresetDeleteResponse extends IPresetResponse {}
