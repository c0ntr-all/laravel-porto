import { api } from 'src/boot/axios'
import { IUpdateTaskPayload, IUpdateTaskResponse } from 'src/types'

export const taskApi = {
  async updateTask(id: string, payload: IUpdateTaskPayload): Promise<IUpdateTaskResponse> {
    const response = await api.patch(`v1/task-manager/tasks/${id}`, payload)
    return response.data
  }
}
