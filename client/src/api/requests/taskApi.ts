import { api } from 'src/boot/axios'
import { IDeleteTaskResponse, IUpdateTaskPayload, IUpdateTaskResponse } from 'src/types'

export const taskApi = {
  async getTaskLists() {
    const response = await api.get('v1/task-manager/task-lists')

    return response.data
  },
  async createTaskList(payload: IUpdateTaskPayload) {
    const response = await api.post('v1/task-manager/task-lists', payload)

    return response.data
  },
  async getTask(id: string) {
    const response = await api.get(`v1/task-manager/tasks/${id}`)

    return response.data
  },
  async createTask(payload: ICreateTaskPayload) {
    const response = await api.post('v1/task-manager/tasks', payload)

    return response.data
  },
  async updateTask(id: string, payload: IUpdateTaskPayload): Promise<IUpdateTaskResponse> {
    const response = await api.patch(`v1/task-manager/tasks/${id}`, payload)

    return response.data
  },
  async deleteTask(id: string): Promise<IDeleteTaskResponse> {
    const response = await api.delete(`v1/task-manager/tasks/${id}`)

    return response.data
  },

  async createChecklist(taskId, payload) {
    const url = `v1/task-manager/tasks/${taskId}/checklists`
    const response = await api.post(url, payload)

    return response.data
  },

  async updateChecklist(taskId, checklistId, payload) {
    const url = `v1/task-manager/tasks/${taskId}/checklists/${checklistId}`
    const response = await api.patch(url, payload)

    return response.data
  },

  async createChecklistItem(taskId, checklistId, payload) {
    const url = `v1/task-manager/tasks/${taskId}/checklists/${checklistId}/items`
    const response = await api.post(url, payload)

    return response.data
  },

  async updateChecklistItem(taskId, checklistId, checklistItemId, payload) {
    const url = `v1/task-manager/tasks/${taskId}/checklists/${checklistId}/items/${checklistItemId}`
    const response = await api.patch(url, payload)

    return response.data
  },

  async deleteChecklistItem(taskId, checklistId, checklistItemId) {
    const url = `v1/task-manager/tasks/${taskId}/checklists/${checklistId}/items/${checklistItemId}`
    const response = await api.delete(url)

    return response.data
  },

  async createProgress(taskId, payload) {
    const url = `v1/task-manager/tasks/${taskId}/progress`
    const response = await api.post(url, payload)

    return response.data
  },

  async createReminder(taskId, payload) {
    const url = `v1/task-manager/tasks/${taskId}/reminder`
    const response = await api.post(url, payload)

    return response.data
  }
}
