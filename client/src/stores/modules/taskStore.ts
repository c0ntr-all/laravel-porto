import { defineStore } from 'pinia'
import { reactive } from 'vue'
import { camel } from 'radash'
import { updateObject } from 'src/utils/helpers'
import {
  ITaskList, ITask, IProgress, IChecklist, IChecklistItem, ITaskListCreatePayload, ITaskListUpdatePayload,
  ITaskCreatePayload,
  ITaskUpdatePayload, IChecklistItemCreatePayload, IChecklistCreatePayload,
  IChecklistUpdatePayload, IChecklistItemUpdatePayload, IProgressCreatePayload, IReminderCreatePayload,
  IReminderUpdatePayload, IReminderItem, IReminderOccurrence,
  IUseCaseLog, IComment, ICommentCreatePayload, IFilter, IPostAttachment
} from 'src/types'
import { IUser } from 'src/types/user'
import { StoreEntity } from 'src/types/store'
import {
  upsertEntity, normalizeEntity, normalizeEntityCollection, handleApiSuccess, handleApiError, patchEntity
} from 'src/utils/jsonapi'
import { taskApi } from 'src/api/requests/taskApi'
import { commentApi } from 'src/api/requests/commentApi'
import { useCaseLogApi } from 'src/api/requests/useCaseLogApi'
import { attachmentApi } from 'src/api/requests/attachmentApi'
import { mapTaskAttachments } from 'src/api/mappers/attachment.mapper'
import { TM_TASK_ATTACHABLE_TYPE } from 'src/constants/TaskManager/attachment'
import { mapResponse } from 'src/utils/jsonApiMapper'

export const useTaskStore = defineStore('task', () => {
  const taskLists = reactive<StoreEntity<ITaskList>>({ byId: {}, allIds: [] })
  const tasks = reactive<StoreEntity<ITask>>({ byId: {}, allIds: [] })
  const progress = reactive<StoreEntity<IProgress>>({ byId: {}, allIds: [] })
  const checklists = reactive<StoreEntity<IChecklist>>({ byId: {}, allIds: [] })
  const checklistItems = reactive<StoreEntity<IChecklistItem>>({ byId: {}, allIds: [] })
  const reminder = reactive<StoreEntity<IReminderItem>>({ byId: {}, allIds: [] })
  const reminderOccurrences = reactive<StoreEntity<IReminderOccurrence>>({ byId: {}, allIds: [] })
  const comments = reactive<StoreEntity<IComment>>({ byId: {}, allIds: [] })
  const useCaseLogs = reactive<StoreEntity<IUseCaseLog>>({ byId: {}, allIds: [] })
  const users = reactive<StoreEntity<IUser>>({ byId: {}, allIds: [] })
  const attachments = reactive<StoreEntity<IPostAttachment>>({ byId: {}, allIds: [] })

  type Collections = {
    taskLists: StoreEntity<ITaskList>
    tasks: StoreEntity<ITask>
    progress: StoreEntity<IProgress>
    checklists: StoreEntity<IChecklist>
    checklistItems: StoreEntity<IChecklistItem>
    reminder: StoreEntity<IReminderItem>
    reminderOccurrences: StoreEntity<IReminderOccurrence>
    comments: StoreEntity<IComment>
    useCaseLogs: StoreEntity<IUseCaseLog>
    users: StoreEntity<IUser>
    attachments: StoreEntity<IPostAttachment>
  }

  // Словарь для доступа по имени
  const collections: Collections = {
    taskLists,
    tasks,
    progress,
    checklists,
    checklistItems,
    reminder,
    reminderOccurrences,
    comments,
    useCaseLogs,
    users,
    attachments
  }

  // Type guard для проверки существования ключа в collections
  function isValidCollectionKey(key: string): key is keyof Collections {
    return key in collections
  }

  function collectionKeyForType(type: string): keyof Collections | null {
    if (type === 'reminders') return 'reminder'
    const camelType = camel(type)
    return isValidCollectionKey(camelType) ? camelType : null
  }

  function relatedItems(related: Record<string, unknown>, key: string): unknown[] {
    const value = related[key]
    if (!value) return []
    if (Array.isArray(value)) return value
    return Object.values(value as Record<string, unknown>)
  }

  function upsertMappedAttachments(items: unknown[]): IPostAttachment[] {
    const mapped = mapTaskAttachments(items)
    for (const item of mapped) {
      upsertEntity(attachments, item)
    }
    return mapped
  }

  function addAttachmentIdsToTask(task: ITask, ids: string[]) {
    if (!task.attachmentsIds) {
      task.attachmentsIds = []
    }

    for (const id of ids) {
      if (!task.attachmentsIds.includes(id)) {
        task.attachmentsIds.push(id)
      }
    }
  }

  function removeAttachmentFromTask(taskId: string, attachmentId: string) {
    const task = tasks.byId[taskId]
    if (task?.attachmentsIds) {
      task.attachmentsIds = task.attachmentsIds.filter(id => id !== attachmentId)
    }

    delete attachments.byId[attachmentId]
    attachments.allIds = attachments.allIds.filter(id => id !== attachmentId)
  }

  async function getTaskLists(): Promise<void> {
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
              upsertEntity(users, related[type][id] as unknown as IUser)
            }
            break
          case 'attachments': {
            upsertMappedAttachments(relatedItems(related as Record<string, unknown>, type))
            break
          }
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

      // TODO: prepare empty Task
      entity.checklistsIds = []
      entity.progressIds = []
      entity.reminderIds = []
      entity.commentsIds = []
      entity.attachmentsIds = []
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

  async function updateTask(id: string, payload: ITaskUpdatePayload): Promise<boolean> {
    try {
      const responseData = await taskApi.updateTask(id, payload)
      const { entity, related } = normalizeEntity<ITask>(responseData.data, responseData.included)

      entity.isHydrated = true

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

  async function createReminder(
    taskId: string,
    payload: IReminderCreatePayload
  ): Promise<IReminderItem | undefined> {
    try {
      const responseData = await taskApi.createReminder(taskId, payload)
      const { entity } = normalizeEntity<IReminderItem>(responseData.data, responseData.included)

      upsertEntity(reminder, entity)

      const task = tasks.byId[taskId]
      if (task) {
        if (!task.reminderIds) {
          task.reminderIds = []
        }

        if (!task.reminderIds.includes(entity.id)) {
          task.reminderIds.push(entity.id)
        }

        task.reminders_count++
      }

      handleApiSuccess(responseData)

      return entity
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function updateReminder(
    taskId: string,
    payload: IReminderUpdatePayload
  ): Promise<void> {
    try {
      const responseData = await taskApi.updateReminder(taskId, payload)
      const { entity } = normalizeEntity<IReminderItem>(responseData.data, responseData.included)

      const current = reminder.byId[entity.id]
      const updated = current ? updateObject(current, entity) : entity

      upsertEntity(reminder, updated)

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function deleteReminder(taskId: string): Promise<void> {
    try {
      const task = tasks.byId[taskId]
      const reminderId = task?.reminderIds?.[0]
        || Object.values(reminder.byId).find(item => item.task_id === taskId)?.id

      if (!reminderId) return

      const responseData = await taskApi.deleteReminder(taskId)

      delete reminder.byId[reminderId]
      reminder.allIds = reminder.allIds.filter(id => id !== reminderId)

      reminderOccurrences.allIds = reminderOccurrences.allIds.filter(id => {
        const occurrence = reminderOccurrences.byId[id]
        if (occurrence?.reminder_id === reminderId) {
          delete reminderOccurrences.byId[id]
          return false
        }

        return true
      })

      if (task) {
        task.reminderIds = (task.reminderIds || []).filter(id => id !== reminderId)
        task.reminders_count = Math.max(0, (task.reminders_count || 1) - 1)
      }

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function getReminderOccurrences(taskId: string): Promise<void> {
    try {
      const responseData = await taskApi.getReminderOccurrences(taskId)
      const data = Array.isArray(responseData.data) ? responseData.data : [responseData.data]
      const entities = normalizeEntityCollection(data, responseData.included)

      for (const type in entities) {
        if (type !== 'reminder-occurrences') continue

        for (const id in entities[type]) {
          upsertEntity(reminderOccurrences, entities[type][id] as IReminderOccurrence)
        }
      }
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function completeReminder(taskId: string): Promise<IReminderOccurrence | undefined> {
    try {
      const responseData = await taskApi.createReminderOccurrence(taskId, {
        status: 'completed'
      })
      const { entity, related } = normalizeEntity<IReminderOccurrence>(
        responseData.data,
        responseData.included
      )

      upsertEntity(reminderOccurrences, entity)

      const relatedReminders = (related.reminder || related.reminders) as unknown as IReminderItem[] | undefined
      const includedReminders = relatedReminders?.length
        ? relatedReminders
        : (responseData.included || [])
          .filter(item => item.type === 'reminders' || item.type === 'reminder')
          .map(item => ({
            id: item.id,
            ...(item.attributes || {})
          } as IReminderItem))

      for (const item of includedReminders) {
        if (!item.id) continue
        const current = reminder.byId[item.id]
        upsertEntity(reminder, current ? updateObject(current, item) : item)
      }

      handleApiSuccess(responseData)

      return entity
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function getComments(filters: IFilter): Promise<void> {
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
  }

  async function createComment(payload: ICommentCreatePayload): Promise<void> {
    const responseData = await commentApi.createComment(payload)
    const { entity, related } = normalizeEntity<IComment>(responseData.data, responseData.included)

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
  }

  async function getUseCaseLogs(filters: IFilter): Promise<void> {
    const responseData = await useCaseLogApi.getUseCaseLogs(filters)

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

  async function uploadTaskAttachments(
    taskId: string,
    files: File[],
    onProgress?: (percent: number) => void
  ): Promise<IPostAttachment[]> {
    try {
      const responseData = await attachmentApi.upload(
        TM_TASK_ATTACHABLE_TYPE,
        taskId,
        files,
        undefined,
        { onProgress }
      )

      const stored = upsertMappedAttachments(mapResponse(responseData))
      const task = tasks.byId[taskId]
      if (task) {
        addAttachmentIdsToTask(task, stored.map(item => item.id))
      }

      handleApiSuccess(responseData)
      return stored
    } catch (error: unknown) {
      handleApiError(error)
      return []
    }
  }

  async function deleteTaskAttachment(taskId: string, attachmentId: string): Promise<void> {
    const updated = await updateTask(taskId, {
      deleted_attachments_ids: [attachmentId]
    })
    if (updated) {
      removeAttachmentFromTask(taskId, attachmentId)
    }
  }

  return {
    taskLists,
    tasks,
    progress,
    checklists,
    checklistItems,
    reminder,
    reminderOccurrences,
    comments,
    useCaseLogs,
    users,
    attachments,
    getTaskLists,
    createTaskList,
    updateTaskList,
    deleteTaskList,
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
    updateReminder,
    deleteReminder,
    getReminderOccurrences,
    completeReminder,
    getComments,
    createComment,
    getUseCaseLogs,
    uploadTaskAttachments,
    deleteTaskAttachment
  }
})
