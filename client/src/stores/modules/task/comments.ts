import { camel } from 'radash'
import {
  ITaskList, ITask, IProgress, IChecklist, IChecklistItem,
  IReminderItem, IComment, ICommentCreatePayload, IFilter
} from 'src/types'
import { IUser } from 'src/types/user'
import {
  upsertEntity, normalizeEntity, normalizeEntityCollection, handleApiError
} from 'src/utils/jsonapi'
import { commentApi } from 'src/api/requests/commentApi'
import type { TaskStoreContext } from './collections'

export function createCommentActions(ctx: TaskStoreContext) {
  const {
    taskLists,
    tasks,
    progress,
    checklists,
    checklistItems,
    reminder,
    comments,
    users
  } = ctx.state
  const { collections, isValidCollectionKey } = ctx

  async function getComments(filters: IFilter): Promise<void> {
    try {
      const responseData = await commentApi.getComments(filters)

      const entities = normalizeEntityCollection(responseData.data, responseData.included)

      for (const type in entities) {
        const camelType = camel(type)

        if (isValidCollectionKey(camelType)) {
          for (const id in entities[type]) {
            upsertEntity(collections[camelType], entities[type][id])
          }
        }
      }
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function createComment(payload: ICommentCreatePayload): Promise<void> {
    try {
      const responseData = await commentApi.createComment(payload)
      const resource = Array.isArray(responseData.data) ? responseData.data[0] : responseData.data
      if (!resource) {
        return
      }

      const { entity, related } = normalizeEntity<IComment>(resource, responseData.included)

      upsertEntity(comments, entity)

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
            for (const id in related[type]) {
              upsertEntity(tasks, related[type][id] as unknown as ITask)
            }
            break
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
        }
      }

      // Add task to the taskIds of list
      const task = tasks.byId[payload.commentable_id]
      if (!task.commentsIds) {
        task.commentsIds = []
      }

      if (!task.commentsIds.includes(entity.id)) {
        task.commentsIds.push(entity.id)
      }
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  return {
    getComments,
    createComment
  }
}
