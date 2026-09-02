import { IJsonApiResource, IJsonApiResponse, IUser } from '../jsonapi'

export interface IPresetRules {
  tags?: string[]
  date_from?: string | null
  date_to?: string | null
  ignore_time?: boolean
  text?: string | null
}

export interface IPreset {
  type: string
  id: string
  title?: string
  description?: string
  color: string
  icon: string | null
  start_date?: string | null
  end_date?: string | null
  date_from: string | null
  date_to: string | null
  rules?: IPresetRules
  start_post_id: string | null
  end_post_id: string | null
  tags?: string[]
  created_at: string
  user?: IUser
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
  icon: string | null
  start_date: string | null
  end_date: string | null
  rules?: IPresetRules
  created_at: string
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

export type { IPresetCreateDto } from 'src/api/DTO/PresetCreateDto'
