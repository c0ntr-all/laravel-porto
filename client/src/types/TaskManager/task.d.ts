import { IJsonApiResource, IJsonApiResponse } from 'src/types'

/**
 * Only backend fields
 */
export interface IChecklistItemFields {
  id: string
  title: string
  created_at: string
  finished_at: string | null
  is_declined: boolean
  decline_reason: string | null
}
export interface IChecklistItem extends IChecklistItemFields {
  id: string
}
export interface IChecklistItemCreatePayload {
  title: string
}
export interface IChecklistItemUpdatePayload extends Partial<IChecklistItem> {
  is_finished?: boolean
}
export interface IChecklistItemResource extends IJsonApiResource {
  attributes: IChecklistItemFields
}
export interface IChecklistItemResponse extends IJsonApiResponse<IChecklistItemResource> {
  data: IChecklistItemResource
}
export interface IChecklistItemCreateResponse extends IChecklistItemResponse {}
export interface IChecklistItemUpdateResponse extends IChecklistItemResponse {}
export interface IChecklistItemDeleteResponse extends IChecklistItemResponse {} // TODO: Переделать в просто сообщение

/**
 * Only backend fields
 */
export interface IChecklistFields {
  id: string
  title: string
  created_at: string
  updated_at: string
  checklistItemsIds: string[]
}

export interface IChecklist extends IChecklistFields {
  id: string
}
export interface IChecklistCreatePayload {
  title: string
}
export interface IChecklistUpdatePayload extends Partial<IChecklistFields> {}

export interface IChecklistResource extends IJsonApiResource {
  attributes: IChecklistFields
}
export interface IChecklistResponse extends IJsonApiResponse<IChecklistResource> {
  data: IChecklistResource
}
export interface IChecklistCreateResponse extends IChecklistResponse {}
export interface IChecklistUpdateResponse extends IChecklistResponse {}

/**
 * Only backend fields
 */
export interface IProgressFields {
  id: string
  task_id: string
  title: string
  content: string
  is_final: boolean
  finished_at: string
  created_at: string
  updated_at: string
}
export interface IProgress extends IProgressFields {
  id: string
}
export interface IProgressCreatePayload {
  title: string
  content?: string
  is_final: boolean
  finished_at: string
}
export interface IProgressResource extends IJsonApiResource {
  attributes: IProgressFields
}
export interface IProgressResponse extends IJsonApiResponse<IProgressResource> {
  data: IProgressResource
}
export interface IProgressCreateResponse extends IProgressResponse {}
export interface IProgressUpdateResponse extends IProgressResponse {}

/**
 * Only backend fields
 */
export interface IReminderFields {
  id: string
  task_id: string
  user_id: string
  is_active: boolean
  interval: string
  to_remind_before: string
  datetime: string
  created_at: string
  updated_at: string
}
export interface IReminderItem extends IReminderFields {
  id: string
}
export interface IReminderCreatePayload {
  is_active: boolean
  interval?: string
  to_remind_before?: string
  datetime?: string
}
export interface IReminderResource extends IJsonApiResource {
  attributes: IReminderFields
}
export interface IReminderResponse extends IJsonApiResponse<IReminderResource> {
  data: IReminderResource
}
export interface IReminderCreateResponse extends IReminderResponse {}
export interface IReminderUpdateResponse extends IReminderResponse {}

/**
 * Only backend fields
 */
export interface ITaskFields {
  title: string
  content: string | null
  finished_at: string | null
  is_finished: boolean // Not field, it's for simpler finishing tasks
  is_declined: boolean
  decline_reason: string | null
}
export interface ITask extends ITaskFields {
  id: string
  task_list_id: string
  isHydrated: boolean // If full Task loaded
  checklistsIds?: string[],
  progressIds?: string[],
  reminderIds?: string[],
  commentsIds: string[]
}
export interface ITaskCreatePayload {
  title: string
  content?: string
  task_list_id: string
}
export interface ITaskUpdatePayload extends Partial<ITaskFields> {}
export interface ITaskResource extends IJsonApiResource {
  attributes: ITaskFields
}
export interface ITaskResponse extends IJsonApiResponse<ITaskResource> {
  data: ITaskResource
}
export interface ITaskGetResponse extends ITaskResponse {}
export interface ITaskCreateResponse extends ITaskResponse {}
export interface ITaskUpdateResponse extends ITaskResponse {}
export interface ITaskDeleteResponse extends ITaskResponse {} // TODO: Переделать в просто сообщение

/**
 * Only backend fields
 */
export interface ITaskListFields {
  title: string
}
export interface ITaskList extends ITaskListFields {
  id: string
  title: string
  tasksIds: string[]
}
export interface ITaskListCreatePayload {
  title: string
}
export interface ITaskListUpdatePayload extends Partial<ITaskListFields> {}
export interface ITaskListResource extends IJsonApiResource {
  attributes: ITaskListFields
}
export interface ITaskListResponse extends IJsonApiResponse<ITaskListResource> {
  data: ITaskListResource
}
export interface ITaskListsGetResponse extends ITaskListResponse {
  data: ITaskListResource[]
}
export interface ITaskListGetResponse extends ITaskListResponse {}
export interface ITaskListCreateResponse extends ITaskListResponse {}
export interface ITaskListUpdateResponse extends ITaskListResponse {}
