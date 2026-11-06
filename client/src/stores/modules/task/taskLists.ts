import { updateObject } from 'src/utils/helpers'
import { ITaskList, ITaskListCreatePayload, ITaskListUpdatePayload } from 'src/types'
import {
  upsertEntity, normalizeEntity, normalizeEntityCollection, handleApiSuccess, handleApiError
} from 'src/utils/jsonapi'
import { taskApi } from 'src/api/requests/taskApi'
import type { TaskStoreContext } from './collections'

export function createTaskListActions(ctx: TaskStoreContext) {
  const {
    taskLists,
    tasks,
    reminder,
    reminderOccurrences
  } = ctx.state
  const { collections, collectionKeyForType } = ctx

  async function getTaskLists(): Promise<void> {
    try {
      const responseData = await taskApi.getTaskLists()

      const entities = normalizeEntityCollection(responseData.data, responseData.included)

      for (const type in entities) {
        const collectionKey = collectionKeyForType(type)

        if (collectionKey) {
          for (const id in entities[type]) {
            upsertEntity(collections[collectionKey], entities[type][id])
          }
        }
      }
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function createTaskList(payload: ITaskListCreatePayload): Promise<void> {
    try {
      const responseData = await taskApi.createTaskList(payload)
      const { entity } = normalizeEntity<ITaskList>(responseData.data, responseData.included)

      upsertEntity(taskLists, entity)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function updateTaskList(id: string, payload: ITaskListUpdatePayload): Promise<void> {
    try {
      const responseData = await taskApi.updateTaskList(id, payload)
      const { entity } = normalizeEntity<ITaskList>(responseData.data, responseData.included)

      const current = taskLists.byId[entity.id]
      const updated = current ? updateObject(current, entity) : entity
      if (!updated.tasksIds) {
        updated.tasksIds = current?.tasksIds || []
      }

      upsertEntity(taskLists, updated)

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function deleteTaskList(listId: string): Promise<void> {
    try {
      const list = taskLists.byId[listId]
      if (!list) return

      const responseData = await taskApi.deleteTaskList(listId)
      const taskIds = list.tasksIds || []

      for (const taskId of taskIds) {
        delete tasks.byId[taskId]
      }
      tasks.allIds = tasks.allIds.filter(id => !taskIds.includes(id))

      reminder.allIds = reminder.allIds.filter(id => {
        const item = reminder.byId[id]
        if (item && taskIds.includes(item.task_id)) {
          delete reminder.byId[id]
          return false
        }
        return true
      })

      reminderOccurrences.allIds = reminderOccurrences.allIds.filter(id => {
        const item = reminderOccurrences.byId[id]
        if (item && taskIds.includes(item.task_id)) {
          delete reminderOccurrences.byId[id]
          return false
        }
        return true
      })

      delete taskLists.byId[listId]
      taskLists.allIds = taskLists.allIds.filter(id => id !== listId)

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  return {
    getTaskLists,
    createTaskList,
    updateTaskList,
    deleteTaskList
  }
}
