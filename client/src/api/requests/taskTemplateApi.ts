import { api } from 'src/boot/axios'
import { ITaskTemplatePayload, ITaskTemplateResponse } from 'src/types'

export const taskTemplateApi = {
  async getTaskTemplates(): Promise<ITaskTemplateResponse> {
    const response = await api.get('v1/task-manager/task-templates')

    return response.data
  },

  async getTaskTemplate(id: string): Promise<ITaskTemplateResponse> {
    const response = await api.get(`v1/task-manager/task-templates/${id}`)

    return response.data
  },

  async createTaskTemplate(payload: ITaskTemplatePayload): Promise<ITaskTemplateResponse> {
    const response = await api.post('v1/task-manager/task-templates', payload)

    return response.data
  },

  async updateTaskTemplate(
    id: string,
    payload: ITaskTemplatePayload
  ): Promise<ITaskTemplateResponse> {
    const response = await api.patch(`v1/task-manager/task-templates/${id}`, payload)

    return response.data
  },

  async deleteTaskTemplate(id: string): Promise<ITaskTemplateResponse> {
    const response = await api.delete(`v1/task-manager/task-templates/${id}`)

    return response.data
  }
}
