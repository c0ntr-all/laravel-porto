import { IJsonApiResponse } from 'src/types'

export interface ITaskTemplateChecklistItem {
  id?: string
  title: string
}

export interface ITaskTemplateChecklist {
  id?: string
  title: string
  items: ITaskTemplateChecklistItem[]
}

export interface ITaskTemplate {
  id: string
  title: string
  content: string | null
  checklists: ITaskTemplateChecklist[]
  created_at?: string
  updated_at?: string
}

export interface ITaskTemplateChecklistItemPayload {
  title: string
}

export interface ITaskTemplateChecklistPayload {
  title: string
  items: ITaskTemplateChecklistItemPayload[]
}

export interface ITaskTemplatePayload {
  title: string
  content?: string
  checklists: ITaskTemplateChecklistPayload[]
}

export interface ITaskTemplateChecklistItemFormModel {
  title: string
}

export interface ITaskTemplateChecklistFormModel {
  title: string
  items: ITaskTemplateChecklistItemFormModel[]
}

export interface ITaskTemplateFormModel {
  title: string
  content: string
  checklists: ITaskTemplateChecklistFormModel[]
}

export interface ITaskTemplateResponse extends IJsonApiResponse {}
