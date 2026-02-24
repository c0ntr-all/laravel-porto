import { api } from 'src/boot/axios'
import {
  ITaskListsGetResponse,
  ITaskListCreatePayload,
  ITaskCreatePayload,
  ITaskUpdatePayload,
  ITaskGetResponse,
  ITaskCreateResponse,
  ITaskUpdateResponse,
  ITaskDeleteResponse,
  ITaskListCreateResponse, IChecklistCreatePayload, IChecklistCreateResponse, IChecklistUpdatePayload,
  IChecklistUpdateResponse, IChecklistItemCreatePayload, IChecklistItemCreateResponse, IChecklistItemUpdatePayload,
  IChecklistItemUpdateResponse, IChecklistItemDeleteResponse, IProgressCreatePayload, IProgressCreateResponse,
  IReminderCreatePayload, IReminderCreateResponse
} from 'src/types'

export const taskApi = {
  async getTaskLists(): Promise<ITaskListsGetResponse> {
    const response = await api.get('v1/task-manager/task-lists')

    return response.data
  },
  async createTaskList(payload: ITaskListCreatePayload): Promise<ITaskListCreateResponse> {
    const response = await api.post('v1/task-manager/task-lists', payload)

    return response.data
  },
  async getTask(id: string): Promise<ITaskGetResponse> {
    const response = await api.get(`v1/task-manager/tasks/${id}`)

    return response.data
  },
  async createTask(payload: ITaskCreatePayload): Promise<ITaskCreateResponse> {
    const response = await api.post('v1/task-manager/tasks', payload)

    return response.data
  },
  async updateTask(id: string, payload: ITaskUpdatePayload): Promise<ITaskUpdateResponse> {
    const response = await api.patch(`v1/task-manager/tasks/${id}`, payload)

    return response.data
  },
  async deleteTask(id: string): Promise<ITaskDeleteResponse> {
    const response = await api.delete(`v1/task-manager/tasks/${id}`)

    return response.data
  },

  async createChecklist(taskId: string, payload: IChecklistCreatePayload): Promise<IChecklistCreateResponse> {
    const url = `v1/task-manager/tasks/${taskId}/checklists`
    const response = await api.post(url, payload)

    return response.data
  },

  async updateChecklist(
    taskId: string,
    checklistId: string,
    payload: IChecklistUpdatePayload
  ): Promise<IChecklistUpdateResponse> {
    const url = `v1/task-manager/tasks/${taskId}/checklists/${checklistId}`
    const response = await api.patch(url, payload)

    return response.data
  },

  async createChecklistItem(
    taskId: string,
    checklistId: string,
    payload: IChecklistItemCreatePayload
  ): Promise<IChecklistItemCreateResponse> {
    const url = `v1/task-manager/tasks/${taskId}/checklists/${checklistId}/items`
    const response = await api.post(url, payload)

    return response.data
  },

  async updateChecklistItem(
    taskId: string,
    checklistId: string,
    checklistItemId: string,
    payload: IChecklistItemUpdatePayload
  ): Promise<IChecklistItemUpdateResponse> {
    const url = `v1/task-manager/tasks/${taskId}/checklists/${checklistId}/items/${checklistItemId}`
    const response = await api.patch(url, payload)

    return response.data
  },

  async deleteChecklistItem(
    taskId: string,
    checklistId: string,
    checklistItemId: string
  ): Promise<IChecklistItemDeleteResponse> {
    const url = `v1/task-manager/tasks/${taskId}/checklists/${checklistId}/items/${checklistItemId}`
    const response = await api.delete(url)

    return response.data
  },

  async createProgress(
    taskId: string,
    payload: IProgressCreatePayload
  ): Promise<IProgressCreateResponse> {
    const url = `v1/task-manager/tasks/${taskId}/progress`
    const response = await api.post(url, payload)

    return response.data
  },

  async createReminder(
    taskId: string,
    payload: IReminderCreatePayload
  ): Promise<IReminderCreateResponse> {
    const url = `v1/task-manager/tasks/${taskId}/reminder`
    const response = await api.post(url, payload)

    return response.data
  }
}
