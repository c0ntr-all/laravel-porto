import { api } from 'src/boot/axios'
import { IUpdateTaskPayload, IUpdateTaskResponse } from 'src/types'

export const taskApi = {
  async updateTask(id: string, payload: IUpdateTaskPayload): Promise<IUpdateTaskResponse> {
    const response = await api.patch(`v1/task-manager/tasks/${id}`, payload)

    return response.data
  },

  async updateChecklistItem(taskId, checklistId, checklistItemId, payload) {
    const url = `v1/task-manager/tasks/${taskId}/checklists/${checklistId}/items/${checklistItemId}`
    const response = await api.patch(url, payload)

    return response.data
  }
}
