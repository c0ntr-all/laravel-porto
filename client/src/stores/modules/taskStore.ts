import { defineStore } from 'pinia'
import { reactive } from 'vue'
import { camel } from 'radash'
import { taskApi } from 'src/api/requests/taskApi'
import { updateObject } from 'src/utils/helpers'
import { IComment, ICreateTaskResponse, ITask, ITaskList, IUpdateTaskResponse } from 'src/types/TaskManager/task'
import { StoreEntity } from 'src/types/store'
import {
  upsertEntity,
  normalizeEntity,
  normalizeEntityCollection, handleApiSuccess, handleApiError, patchEntity
} from 'src/utils/jsonapi'
import { ICommentPayload } from 'src/types'
import { commentApi } from 'src/api/requests/commentApi'
import { AxiosError } from 'axios'

export const useTaskStore = defineStore('task', () => {
  const taskLists = reactive<StoreEntity<ITaskList>>({
    byId: {},
    allIds: []
  })

  const tasks = reactive<StoreEntity<ITask>>({
    byId: {},
    allIds: []
  })

  const progress = reactive<StoreEntity<IProgress>>({
    byId: {},
    allIds: []
  })

  const checklists = reactive<StoreEntity<IChecklist>>({
    byId: {},
    allIds: []
  })

  const checklistItems = reactive<StoreEntity<IChecklistItem>>({
    byId: {},
    allIds: []
  })

  const comments = reactive<StoreEntity<IComment>>({
    byId: {},
    allIds: []
  })

  const user = reactive<StoreEntity<IUser>>({
    byId: {},
    allIds: []
  })
  async function getTaskLists() {
    const responseData = await taskApi.getTaskLists()

    const entities = normalizeEntityCollection(responseData.data, responseData.included)

    for (const type in entities) {
      const camelType = camel(type)
      for (const id in entities[type]) {
        upsertEntity(this[camelType], entities[type][id])
      }
    }
  }

  async function createTaskList(payload: {title: string}) {
    try {
      const responseData = await taskApi.createTaskList(payload)
      const { entity } = normalizeEntity(responseData.data, responseData.included)

      entity.tasksIds = []

      upsertEntity(this.taskLists, entity)
    } catch (error: AxiosError<{ message: string }>) {
      handleApiError(error)
    }
  }

  async function getTask(id: string): Promise<void> {
    try {
      const responseData = await taskApi.getTask<ITaskResponse>(id)
      const { entity, related } = normalizeEntity(responseData.data, responseData.included)

      for (const type in related) {
        const camelType = camel(type)
        for (const id in related[type]) {
          upsertEntity(this[camelType], related[type][id])
        }
      }

      entity.isHydrated = true

      upsertEntity(this.tasks, entity)
    } catch (error: AxiosError<{ message: string }>) {
      handleApiError(error)
    }
  }

  async function createTask(payload: {title: string; task_list_id: number}): Promise<void> {
    try {
      const responseData = await taskApi.createTask<ICreateTaskResponse>(payload)
      const { entity } = normalizeEntity(responseData.data, responseData.included)

      entity.isHydrated = true

      upsertEntity(this.tasks, entity)

      // Add task to the taskIds of list
      const list = taskLists.byId[entity.task_list_id]
      if (!list.tasksIds.includes(entity.id)) {
        list.tasksIds.push(entity.id)
      }
    } catch (error: AxiosError<{ message: string }>) {
      handleApiError(error)
    }
  }

  async function updateTask(id: string, payload: object): Promise<IUpdateTaskResponse> {
    try {
      const responseData = await taskApi.updateTask(id, payload)
      const { entity } = normalizeEntity(responseData.data, responseData.included)

      entity.isHydrated = true

      const task = this.tasks.byId[entity.id]
      const updated = updateObject(task, entity)

      upsertEntity(this.tasks, updated)

      handleApiSuccess(responseData)
    } catch (error: AxiosError<{ message: string }>) {
      handleApiError(error)
    }
  }

  async function deleteTask(taskId: string) {
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

  async function createChecklist(taskId, payload) {
    try {
      const responseData = await taskApi.createChecklist(taskId, payload)
      const { entity } = normalizeEntity(responseData.data, responseData.included)

      entity.checklistItemsIds = []

      upsertEntity(this.checklists, entity)

      const task = tasks.byId[taskId]
      if (!task.checklistsIds.includes(entity.id)) {
        task.checklistsIds.push(entity.id)
      }

      handleApiSuccess(responseData)
    } catch (error: AxiosError<{ message: string }>) {
      handleApiError(error)
    }
  }

  async function updateChecklist(taskId, checklistId, payload) {
    try {
      const responseData = await taskApi.updateChecklist(taskId, checklistId, payload)
      const { entity } = normalizeEntity(responseData.data, responseData.included)

      patchEntity(this.checklists, entity)

      handleApiSuccess(responseData)
    } catch (error) {
      handleApiError(error)
    }
  }

  async function createChecklistItem(taskId, checklistId, payload) {
    try {
      const responseData = await taskApi.createChecklistItem(taskId, checklistId, payload)
      const { entity } = normalizeEntity(responseData.data, responseData.included)

      upsertEntity(this.checklistItems, entity)

      const checklist = checklists.byId[checklistId]
      if (!checklist.checklistItemsIds.includes(entity.id)) {
        checklist.checklistItemsIds.push(entity.id)
      }

      handleApiSuccess(responseData)
    } catch (error: AxiosError<{ message: string }>) {
      handleApiError(error)
    }
  }

  async function updateChecklistItem(taskId, checklistId, checklistItemId, payload) {
    try {
      const responseData = await taskApi.updateChecklistItem(taskId, checklistId, checklistItemId, payload)
      const { entity } = normalizeEntity(responseData.data, responseData.included)

      patchEntity(this.checklistItems, entity)

      handleApiSuccess(responseData)
    } catch (error) {
      handleApiError(error)
    }
  }

  async function finishChecklistItem(taskId, checklistId, checklistItemId) {
    try {
      // Optimistic UI
      checklistItems.byId[checklistItemId].finished_at = Date().toString()

      const responseData = await taskApi.updateChecklistItem(taskId, checklistId, checklistItemId, {
        is_finished: true
      })
      const { entity } = normalizeEntity(responseData.data, responseData.included)

      patchEntity(this.checklistItems, entity)

      handleApiSuccess(responseData)
    } catch (error) {
      // Revert Optimistic UI
      checklistItems.byId[checklistItemId].finished_at = null

      handleApiError(error)
    }
  }

  async function unfinishChecklistItem(taskId, checklistId, checklistItemId) {
    try {
      // Optimistic UI
      checklistItems.byId[checklistItemId].finished_at = null

      const responseData = await taskApi.updateChecklistItem(taskId, checklistId, checklistItemId, {
        is_finished: false
      })
      const { entity } = normalizeEntity(responseData.data, responseData.included)

      patchEntity(this.checklistItems, entity)

      handleApiSuccess(responseData)
    } catch (error) {
      // Revert Optimistic UI
      checklistItems.byId[checklistItemId].finished_at = Date().toString()

      handleApiError(error)
    }
  }

  async function deleteChecklistItem(taskId: string, checklistId: string, checklistItemId: string) {
    try {
      const checklistItem = checklistItems.byId[checklistItemId]
      if (!checklistItem) return

      const responseData = await taskApi.deleteChecklistItem(taskId, checklistId, checklistItemId)

      const checklist = checklists.byId[checklistId]
      if (checklist) {
        checklist.checklistItemsIds = checklist.checklistItemsIds.filter(id => id !== checklistItemId)
      }

      delete checklistItems.byId[checklistItemId]
      checklistItems.allIds = checklistItems.allIds.filter(id => id !== checklistItemId)

      handleApiSuccess(responseData)
    } catch (error) {
      handleApiError(error)
    }
  }

  async function createProgress(taskId, payload) {
    try {
      const responseData = await taskApi.createProgress(taskId, payload)
      const { entity } = normalizeEntity(responseData.data, responseData.included)

      upsertEntity(this.progress, entity)

      const task = tasks.byId[taskId]
      if (!task.progressIds.includes(entity.id)) {
        task.progressIds.push(entity.id)
      }

      handleApiSuccess(responseData)

      return entity
    } catch (error: AxiosError<{ message: string }>) {
      handleApiError(error)
    }
  }

  async function createReminder(taskId, payload) {
    try {
      const responseData = await taskApi.createReminder(taskId, payload)
      const { entity } = normalizeEntity(responseData.data, responseData.included)

      upsertEntity(this.reminder, entity)

      const task = tasks.byId[taskId]
      if (!task.reminderIds.includes(entity.id)) {
        task.reminderIds.push(entity.id)
      }

      handleApiSuccess(responseData)
    } catch (error: AxiosError<{ message: string }>) {
      handleApiError(error)
    }
  }

  async function createComment(payload: ICommentPayload) {
    const responseData = await commentApi.createComment(payload)
    const { entity } = normalizeEntity(responseData.data, responseData.included)

    upsertEntity(this.comments, entity)

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
