import { camel } from 'radash'
import { updateObject } from 'src/utils/helpers'
import {
  ITaskList, ITask, IProgress, IChecklist, IChecklistItem,
  ITaskCreatePayload,
  ITaskUpdatePayload, IProgressCreatePayload, IReminderItem,
  IComment
} from 'src/types'
import { IUser } from 'src/types/user'
import { StoreEntity } from 'src/types/store'
import {
  upsertEntity, normalizeEntity, normalizeEntityCollection, handleApiSuccess, handleApiError, patchEntity
} from 'src/utils/jsonapi'
import { taskApi } from 'src/api/requests/taskApi'
import type { TaskStoreContext } from './collections'

export function prepareEmptyTaskRelations(entity: ITask) {
  entity.checklistsIds = entity.checklistsIds || []
  entity.progressIds = entity.progressIds || []
  entity.reminderIds = entity.reminderIds || []
  entity.commentsIds = entity.commentsIds || []
  entity.attachmentsIds = entity.attachmentsIds || []
  entity.subtasksIds = entity.subtasksIds || []
}

export function upsertRelatedTask(tasks: StoreEntity<ITask>, relatedTask: ITask) {
  const existing = tasks.byId[relatedTask.id]
  if (existing?.isHydrated) {
    return
  }

  prepareEmptyTaskRelations(relatedTask)
  relatedTask.isHydrated = false
  upsertEntity(tasks, relatedTask)
}

export function linkSubtaskToParent(tasks: StoreEntity<ITask>, parentId: string, subtaskId: string) {
  const parent = tasks.byId[parentId]
  if (!parent) return

  if (!parent.subtasksIds) {
    parent.subtasksIds = []
  }

  if (!parent.subtasksIds.includes(subtaskId)) {
    parent.subtasksIds.push(subtaskId)
    parent.subtasks_count = (parent.subtasks_count || 0) + 1
  }
}

export function createTaskActions(ctx: TaskStoreContext) {
  const {
    taskLists,
    tasks,
    progress,
    checklists,
    checklistItems,
    reminder,
    comments,
    users,
    attachments
  } = ctx.state
  const { relatedItems, upsertMappedAttachments } = ctx

  async function getTask(id: string): Promise<void> {
    try {
      const responseData = await taskApi.getTask(id)
      const { entity, related } = normalizeEntity<ITask>(responseData.data, responseData.included)

      for (const type in related) {
        const camelType = camel(type)

        // TODO: Временная явная проверка для каждого типа
        switch (camelType) {
          case 'taskLists':
            for (const id in related[type]) {
              upsertEntity(taskLists, related[type][id] as unknown as ITaskList)
            }
            break
          case 'tasks':
          case 'subtasks':
          case 'parent':
            for (const id in related[type]) {
              upsertRelatedTask(tasks, related[type][id] as unknown as ITask)
            }
            break
          case 'checklists':
            for (const id in related[type]) {
              upsertEntity(checklists, related[type][id] as unknown as IChecklist)
            }
            break
          case 'checklistItems':
          case 'sourceChecklistItem':
            for (const id in related[type]) {
              upsertEntity(checklistItems, related[type][id] as unknown as IChecklistItem)
            }
            break
          case 'progress':
            for (const id in related[type]) {
              upsertEntity(progress, related[type][id] as unknown as IProgress)
            }
            break
          case 'reminder':
            for (const id in related[type]) {
              upsertEntity(reminder, related[type][id] as unknown as IReminderItem)
            }
            break
          case 'comments':
            for (const id in related[type]) {
              upsertEntity(comments, related[type][id] as unknown as IComment)
            }
            break
          case 'user':
            for (const id in related[type]) {
              upsertEntity(users, related[type][id] as unknown as IUser)
            }
            break
          case 'attachments': {
            upsertMappedAttachments(relatedItems(related as Record<string, unknown>, type))
            break
          }
        }
      }

      prepareEmptyTaskRelations(entity)
      entity.isHydrated = true

      upsertEntity(tasks, entity)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function createTask(payload: ITaskCreatePayload): Promise<ITask | undefined> {
    try {
      const responseData = await taskApi.createTask(payload)
      const { entity, related } = normalizeEntity<ITask>(responseData.data, responseData.included)

      for (const type in related) {
        const camelType = camel(type)

        switch (camelType) {
          case 'checklists':
            for (const id in related[type]) {
              upsertEntity(checklists, related[type][id] as unknown as IChecklist)
            }
            break
          case 'checklistItems':
            for (const id in related[type]) {
              upsertEntity(checklistItems, related[type][id] as unknown as IChecklistItem)
            }
            break
          case 'attachments':
            upsertMappedAttachments(relatedItems(related as Record<string, unknown>, type))
            break
        }
      }

      prepareEmptyTaskRelations(entity)
      entity.isHydrated = Boolean(payload.task_template_id) || !entity.parent_id

      upsertEntity(tasks, entity)

      if (entity.parent_id) {
        linkSubtaskToParent(tasks, entity.parent_id, entity.id)
      } else if (entity.task_list_id) {
        const list = taskLists.byId[entity.task_list_id]
        if (list && !list.tasksIds.includes(entity.id)) {
          list.tasksIds.push(entity.id)
        }
      }

      return entity
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function updateTask(id: string, payload: ITaskUpdatePayload): Promise<boolean> {
    try {
      const responseData = await taskApi.updateTask(id, payload)
      const { entity, related } = normalizeEntity<ITask>(responseData.data, responseData.included)

      // Keep existing hydration when updating from list/card actions
      const previous = tasks.byId[entity.id]
      entity.isHydrated = previous?.isHydrated ?? false

      if (related.attachments) {
        upsertMappedAttachments(relatedItems(related as Record<string, unknown>, 'attachments'))
      }

      const task = tasks.byId[entity.id]
      const updated = updateObject(task, entity)
      if (entity.attachmentsIds) {
        updated.attachmentsIds = entity.attachmentsIds
      }

      upsertEntity(tasks, updated)

      handleApiSuccess(responseData)
      return true
    } catch (error: unknown) {
      handleApiError(error)
      return false
    }
  }

  async function toggleTaskFavorite(taskId: string): Promise<boolean> {
    const task = tasks.byId[taskId]
    if (!task) return false

    const nextFavorite = !task.is_favorite
    const previousFavorite = task.is_favorite
    task.is_favorite = nextFavorite

    try {
      const responseData = await taskApi.updateTask(taskId, { is_favorite: nextFavorite })
      const { entity } = normalizeEntity<ITask>(responseData.data, responseData.included)
      patchEntity(tasks, {
        id: entity.id,
        is_favorite: entity.is_favorite,
        priority: entity.priority
      } as ITask)

      return true
    } catch (error: unknown) {
      task.is_favorite = previousFavorite
      handleApiError(error)
      return false
    }
  }

  async function reorderTasks(orderedIds: string[]): Promise<boolean> {
    if (orderedIds.length < 2) return true

    const previousPriorities = new Map<string, number>()
    orderedIds.forEach((id, index) => {
      const task = tasks.byId[id]
      if (!task) return
      previousPriorities.set(id, task.priority)
      task.priority = index
    })

    try {
      const responseData = await taskApi.reorderTasks({ ids: orderedIds })
      const entities = normalizeEntityCollection(responseData.data, responseData.included)

      if (entities.tasks) {
        for (const id in entities.tasks) {
          const entity = entities.tasks[id] as ITask
          patchEntity(tasks, {
            id: entity.id,
            priority: entity.priority,
            is_favorite: entity.is_favorite
          } as ITask)
        }
      } else {
        orderedIds.forEach((id, index) => {
          const task = tasks.byId[id]
          if (task) task.priority = index
        })
      }

      return true
    } catch (error: unknown) {
      for (const [id, priority] of previousPriorities) {
        const task = tasks.byId[id]
        if (task) task.priority = priority
      }
      handleApiError(error)
      return false
    }
  }

  async function deleteTask(taskId: string): Promise<void> {
    try {
      const task = tasks.byId[taskId]
      if (!task) return

      const responseData = await taskApi.deleteTask(taskId)

      if (task.task_list_id) {
        const list = taskLists.byId[task.task_list_id]
        if (list) {
          list.tasksIds = list.tasksIds.filter(id => id !== taskId)
        }
      }

      delete tasks.byId[taskId]
      tasks.allIds = tasks.allIds.filter(id => id !== taskId)

      const attachmentIds = task.attachmentsIds || []
      for (const attachmentId of attachmentIds) {
        delete attachments.byId[attachmentId]
      }
      attachments.allIds = attachments.allIds.filter(id => !attachmentIds.includes(id))

      handleApiSuccess(responseData)
    } catch (error) {
      handleApiError(error)
    }
  }

  async function createProgress(
    taskId: string,
    payload: IProgressCreatePayload
  ): Promise<IProgress | undefined> {
    try {
      const responseData = await taskApi.createProgress(taskId, payload)
      const { entity } = normalizeEntity<IProgress>(responseData.data, responseData.included)

      upsertEntity(progress, entity)

      const task = tasks.byId[taskId]
      if (!task.progressIds!.includes(entity.id)) {
        task.progressIds!.push(entity.id)
      }

      task.progresses_count++

      handleApiSuccess(responseData)

      return entity
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  return {
    getTask,
    createTask,
    updateTask,
    toggleTaskFavorite,
    reorderTasks,
    deleteTask,
    createProgress
  }
}
