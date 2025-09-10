import { api } from 'src/boot/axios'

export const taskApi = {
  async updateTask(id: string, payload: object): Promise<object> {
    const response = await api.patch(`v1/task-manager/tasks/${id}`, payload)
    return response.data
  }
}
