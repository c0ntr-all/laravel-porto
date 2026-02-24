import { defineStore } from 'pinia'
import { reactive } from 'vue'
import { camel } from 'radash'
import { taskApi } from 'src/api/requests/taskApi'
import { updateObject } from 'src/utils/helpers'
import {
  ITaskList, ITask, IProgress, IChecklist, IChecklistItem, ITaskListCreatePayload, ITaskCreatePayload,
  ITaskUpdatePayload, IChecklistItemCreatePayload, IChecklistCreatePayload,
  IChecklistUpdatePayload, IChecklistItemUpdatePayload, IProgressCreatePayload, IReminderCreatePayload, IReminderItem
} from 'src/types/TaskManager/task'
import { IUser } from 'src/types/user'
import { StoreEntity } from 'src/types/store'
import {
  upsertEntity, normalizeEntity, normalizeEntityCollection, handleApiSuccess, handleApiError, patchEntity
} from 'src/utils/jsonapi'
import { IComment, ICommentCreatePayload } from 'src/types'
import { commentApi } from 'src/api/requests/commentApi'

export const useTaskStore = defineStore('task', () => {
  const taskLists = reactive<StoreEntity<ITaskList>>({ byId: {}, allIds: [] })
  const tasks = reactive<StoreEntity<ITask>>({ byId: {}, allIds: [] })
  const progress = reactive<StoreEntity<IProgress>>({ byId: {}, allIds: [] })
  const checklists = reactive<StoreEntity<IChecklist>>({ byId: {}, allIds: [] })
  const checklistItems = reactive<StoreEntity<IChecklistItem>>({ byId: {}, allIds: [] })
  const reminder = reactive<StoreEntity<IReminderItem>>({ byId: {}, allIds: [] })
  const comments = reactive<StoreEntity<IComment>>({ byId: {}, allIds: [] })
  const user = reactive<StoreEntity<IUser>>({ byId: {}, allIds: [] })

  type Collections = {
    taskLists: StoreEntity<ITaskList>
    tasks: StoreEntity<ITask>
    progress: StoreEntity<IProgress>
    checklists: StoreEntity<IChecklist>
    checklistItems: StoreEntity<IChecklistItem>
    reminder: StoreEntity<IReminderItem> // Исправлено: IReminderItem -> IReminderItem
    comments: StoreEntity<IComment>
    user: StoreEntity<IUser>
  }

  // Словарь для доступа по имени
  const collections: Collections = {
    taskLists,
    tasks,
    progress,
    checklists,
    checklistItems,
    reminder,
    comments,
    user
  }

  // Type guard для проверки существования ключа в collections
  function isValidCollectionKey(key: string): key is keyof Collections {
    return key in collections
  }

  async function getTaskLists(): Promise<void> {
    const responseData = await taskApi.getTaskLists()

    const entities = normalizeEntityCollection(responseData.data, responseData.included)

    for (const type in entities) {
      const camelType = camel(type)

      if (isValidCollectionKey(camelType)) {
        for (const id in entities[type]) {
          upsertEntity(collections[camelType], entities[type][id])
        }
      }
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
              upsertEntity(user, related[type][id] as unknown as IUser)
            }
            break
        }
      }

      entity.isHydrated = true

      upsertEntity(tasks, entity)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function createTask(payload: ITaskCreatePayload): Promise<void> {
    try {
      const responseData = await taskApi.createTask(payload)
      const { entity } = normalizeEntity<ITask>(responseData.data, responseData.included)

      entity.isHydrated = true

      upsertEntity(tasks, entity)

      // Add task to the taskIds of list
      const list = taskLists.byId[entity.task_list_id]
      if (!list.tasksIds.includes(entity.id)) {
        list.tasksIds.push(entity.id)
      }
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function updateTask(id: string, payload: ITaskUpdatePayload): Promise<void> {
    try {
      const responseData = await taskApi.updateTask(id, payload)
      const { entity } = normalizeEntity<ITask>(responseData.data, responseData.included)

      entity.isHydrated = true

      const task = tasks.byId[entity.id]
      const updated = updateObject(task, entity)

      upsertEntity(tasks, updated)

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function deleteTask(taskId: string): Promise<void> {
    try {
      const task = tasks.byId[taskId]
      if (!task) return

      const responseData = await taskApi.deleteTask(taskId)

      const list = taskLists.byId[task.task_list_id]
      if (list) {
        list.tasksIds = list.tasksIds.filter(id => id !== taskId)
      }

      delete tasks.byId[taskId]
      tasks.allIds = tasks.allIds.filter(id => id !== taskId)

      handleApiSuccess(responseData)
    } catch (error) {
      handleApiError(error)
    }
  }

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

      handleApiSuccess(responseData)

      return entity
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function createReminder(
    taskId: string,
    payload: IReminderCreatePayload
  ): Promise<void> {
    try {
      const responseData = await taskApi.createReminder(taskId, payload)
      const { entity } = normalizeEntity<IReminderItem>(responseData.data, responseData.included)

      upsertEntity(reminder, entity)

      const task = tasks.byId[taskId]
      if (task.reminderIds && !task.reminderIds.includes(entity.id)) {
        task.reminderIds.push(entity.id)
      }

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function createComment(payload: ICommentCreatePayload): Promise<void> {
    const responseData = await commentApi.createComment(payload)
    const { entity } = normalizeEntity<IComment>(responseData.data, responseData.included)

    upsertEntity(comments, entity)

    // Add task to the taskIds of list
    const task = tasks.byId[payload.commentable_id]
    if (!task.commentsIds.includes(entity.id)) {
      task.commentsIds.push(entity.id)
    }
  }

  return {
    taskLists,
    tasks,
    progress,
    checklists,
    checklistItems,
    reminder,
    comments,
    user,
    getTaskLists,
    createTaskList,
    getTask,
    createTask,
    updateTask,
    deleteTask,
    createChecklist,
    updateChecklist,
    createChecklistItem,
    updateChecklistItem,
    finishChecklistItem,
    unfinishChecklistItem,
    deleteChecklistItem,
    createProgress,
    createReminder,
    createComment
  }
})
