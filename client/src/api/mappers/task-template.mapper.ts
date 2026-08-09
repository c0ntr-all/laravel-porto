import { IJsonApiResponse } from 'src/types'
import { mapResponse } from 'src/utils/jsonApiMapper'
import {
  ITaskTemplate,
  ITaskTemplateChecklist,
  ITaskTemplateChecklistFormModel,
  ITaskTemplateChecklistItem,
  ITaskTemplateFormModel,
  ITaskTemplatePayload
} from 'src/types/TaskManager/task-template'

function normalizeItems(items: unknown): ITaskTemplateChecklistItem[] {
  if (!Array.isArray(items)) return []

  return items.map((item, index) => ({
    id: item?.id ?? String(index),
    title: item?.title ?? ''
  }))
}

function normalizeChecklists(checklists: unknown): ITaskTemplateChecklist[] {
  if (!Array.isArray(checklists)) return []

  return checklists.map((checklist, index) => ({
    id: checklist?.id ?? String(index),
    title: checklist?.title ?? '',
    items: normalizeItems(checklist?.items)
  }))
}

function normalizeTaskTemplate(raw: Record<string, unknown>): ITaskTemplate {
  return {
    id: String(raw.id),
    title: String(raw.title ?? ''),
    content: raw.content == null ? null : String(raw.content),
    checklists: normalizeChecklists(raw.checklists),
    created_at: raw.created_at ? String(raw.created_at) : undefined,
    updated_at: raw.updated_at ? String(raw.updated_at) : undefined
  }
}

export function mapTaskTemplateResponse(response: IJsonApiResponse): ITaskTemplate {
  const [raw] = mapResponse(response)

  return normalizeTaskTemplate(raw)
}

export function mapTaskTemplatesResponse(response: IJsonApiResponse): ITaskTemplate[] {
  return mapResponse(response).map(normalizeTaskTemplate)
}

export function createEmptyChecklist(): ITaskTemplateChecklistFormModel {
  return {
    title: '',
    items: [{ title: '' }]
  }
}

export function createEmptyTaskTemplateForm(): ITaskTemplateFormModel {
  return {
    title: '',
    content: '',
    checklists: [createEmptyChecklist()]
  }
}

export function cloneTaskTemplateFormModel(
  model: ITaskTemplateFormModel
): ITaskTemplateFormModel {
  return {
    title: model.title,
    content: model.content,
    checklists: model.checklists.map(checklist => ({
      title: checklist.title,
      items: checklist.items.map(item => ({ title: item.title }))
    }))
  }
}

export function mapTaskTemplateFormToPayload(model: ITaskTemplateFormModel): ITaskTemplatePayload {
  return {
    title: model.title.trim(),
    content: model.content.trim(),
    checklists: model.checklists
      .filter(checklist => checklist.title.trim())
      .map(checklist => ({
        title: checklist.title.trim(),
        items: checklist.items
          .filter(item => item.title.trim())
          .map(item => ({ title: item.title.trim() }))
      }))
  }
}

export function mapTaskTemplateToForm(template: ITaskTemplate): ITaskTemplateFormModel {
  return {
    title: template.title,
    content: template.content ?? '',
    checklists: template.checklists.length
      ? template.checklists.map(checklist => ({
        title: checklist.title,
        items: checklist.items.length
          ? checklist.items.map(item => ({ title: item.title }))
          : [{ title: '' }]
      }))
      : [createEmptyChecklist()]
  }
}
