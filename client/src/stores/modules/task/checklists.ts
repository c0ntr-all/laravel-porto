import {
  IChecklist, IChecklistItem, IChecklistItemCreatePayload, IChecklistCreatePayload,
  IChecklistUpdatePayload, IChecklistItemUpdatePayload, ITask
} from 'src/types'
import {
  upsertEntity, normalizeEntity, handleApiSuccess, handleApiError, patchEntity
} from 'src/utils/jsonapi'
import { taskApi } from 'src/api/requests/taskApi'
import type { TaskStoreContext } from './collections'
import { linkSubtaskToParent, prepareEmptyTaskRelations, upsertRelatedTask } from './tasks'

export function createChecklistActions(ctx: TaskStoreContext) {
  const {
    tasks,
    checklists,
    checklistItems
  } = ctx.state

  async function createChecklist(taskId: string, payload: IChecklistCreatePayload): Promise<void> {
    try {
      const responseData = await taskApi.createChecklist(taskId, payload)
      const { entity } = normalizeEntity<IChecklist>(responseData.data, responseData.included)

      entity.checklistItemsIds = []

      upsertEntity(checklists, entity)

      const task = tasks.byId[taskId]
      if (!task.checklistsIds!.includes(entity.id)) {
        task.checklistsIds!.push(entity.id)
      }

      task.checklists_count++

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function updateChecklist(
    taskId: string,
    checklistId: string,
    payload: IChecklistUpdatePayload
  ): Promise<void> {
    try {
      const responseData = await taskApi.updateChecklist(taskId, checklistId, payload)
      const { entity } = normalizeEntity<IChecklist>(responseData.data, responseData.included)

      patchEntity(checklists, entity)

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function createChecklistItem(
    taskId: string,
    checklistId: string,
    payload: IChecklistItemCreatePayload
  ): Promise<void> {
    try {
      const responseData = await taskApi.createChecklistItem(taskId, checklistId, payload)
      const { entity } = normalizeEntity<IChecklistItem>(responseData.data, responseData.included)

      upsertEntity(checklistItems, entity)

      const checklist = checklists.byId[checklistId]
      if (!checklist.checklistItemsIds.includes(entity.id)) {
        checklist.checklistItemsIds.push(entity.id)
      }

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function updateChecklistItem(
    taskId: string,
    checklistId: string,
    checklistItemId: string,
    payload: IChecklistItemUpdatePayload
  ): Promise<void> {
    try {
      const responseData = await taskApi.updateChecklistItem(taskId, checklistId, checklistItemId, payload)
      const { entity } = normalizeEntity<IChecklistItem>(responseData.data, responseData.included)

      patchEntity(checklistItems, entity)

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function finishChecklistItem(
    taskId: string,
    checklistId: string,
    checklistItemId: string
  ): Promise<void> {
    try {
      // Optimistic UI
      checklistItems.byId[checklistItemId].finished_at = Date().toString()

      const responseData = await taskApi.updateChecklistItem(taskId, checklistId, checklistItemId, {
        is_finished: true
      })
      const { entity } = normalizeEntity<IChecklistItem>(responseData.data, responseData.included)

      patchEntity(checklistItems, entity)

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      // Revert Optimistic UI
      checklistItems.byId[checklistItemId].finished_at = null

      handleApiError(error)
    }
  }

  async function unfinishChecklistItem(
    taskId: string,
    checklistId: string,
    checklistItemId: string
  ): Promise<void> {
    try {
      // Optimistic UI
      checklistItems.byId[checklistItemId].finished_at = null

      const responseData = await taskApi.updateChecklistItem(taskId, checklistId, checklistItemId, {
        is_finished: false
      })
      const { entity } = normalizeEntity<IChecklistItem>(responseData.data, responseData.included)

      patchEntity(checklistItems, entity)

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      // Revert Optimistic UI
      checklistItems.byId[checklistItemId].finished_at = Date().toString()

      handleApiError(error)
    }
  }

  async function deleteChecklistItem(
    taskId: string,
    checklistId: string,
    checklistItemId: string
  ): Promise<void> {
    try {
      const checklistItem = checklistItems.byId[checklistItemId]
      if (!checklistItem) return

      const responseData = await taskApi.deleteChecklistItem(taskId, checklistId, checklistItemId)

      const checklist = checklists.byId[checklistId]
      if (checklist) {
        checklist.checklistItemsIds = checklist.checklistItemsIds.filter((id: string) => id !== checklistItemId)
      }

      delete checklistItems.byId[checklistItemId]
      checklistItems.allIds = checklistItems.allIds.filter(id => id !== checklistItemId)

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function convertChecklistItemToTask(
    taskId: string,
    checklistId: string,
    checklistItemId: string
  ): Promise<ITask | undefined> {
    try {
      const responseData = await taskApi.convertChecklistItemToTask(taskId, checklistId, checklistItemId)
      const { entity, related } = normalizeEntity<ITask>(responseData.data, responseData.included)

      prepareEmptyTaskRelations(entity)
      entity.isHydrated = false
      upsertEntity(tasks, entity)

      if (entity.parent_id) {
        linkSubtaskToParent(tasks, entity.parent_id, entity.id)
      } else {
        linkSubtaskToParent(tasks, taskId, entity.id)
      }

      for (const type in related) {
        if (type === 'parent') {
          for (const id in related[type]) {
            upsertRelatedTask(tasks, related[type][id] as unknown as ITask)
          }
        }

        if (type === 'sourceChecklistItem') {
          for (const id in related[type]) {
            upsertEntity(checklistItems, related[type][id] as unknown as IChecklistItem)
          }
        }
      }

      const sourceItem = checklistItems.byId[checklistItemId]
      if (sourceItem) {
        sourceItem.converted_task_id = entity.id
      }

      handleApiSuccess(responseData)
      return entity
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  return {
    createChecklist,
    updateChecklist,
    createChecklistItem,
    updateChecklistItem,
    finishChecklistItem,
    unfinishChecklistItem,
    deleteChecklistItem,
    convertChecklistItemToTask
  }
}
