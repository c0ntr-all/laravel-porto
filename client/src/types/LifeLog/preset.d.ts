import { IJsonApiResource, IJsonApiResponse } from '../jsonapi'
import { IUser } from '../user'

export interface IPreset {
  type: string
  id: string
  title?: string
  description?: string
  color: string
  icon: string | null
  date_from: string | null
  date_to: string | null
  start_post_id: string | null
  end_post_id: string | null
  created_at: string
  user: IUser
}

export interface IPresetModel {
  title: string
  color: string
  date_from?: string | null
  date_to?: string | null
  tags?: string[]
}

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
