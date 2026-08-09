import { defineStore } from 'pinia'
import { ref } from 'vue'
import { taskTemplateApi } from 'src/api/requests/taskTemplateApi'
import {
  mapTaskTemplateResponse,
  mapTaskTemplatesResponse
} from 'src/api/mappers/task-template.mapper'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { ITaskTemplate, ITaskTemplatePayload } from 'src/types'

export const useTaskTemplateStore = defineStore('taskTemplate', () => {
  const templates = ref<ITaskTemplate[]>([])
  const isLoading = ref<boolean>(false)
  const error = ref<string | null>(null)

  async function getTaskTemplates(): Promise<void> {
    isLoading.value = true
    error.value = null

    try {
      const response = await taskTemplateApi.getTaskTemplates()
      templates.value = mapTaskTemplatesResponse(response)
    } catch (err: any) {
      error.value = err.message ?? 'Ошибка загрузки шаблонов'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function getTaskTemplate(id: string): Promise<ITaskTemplate> {
    const response = await taskTemplateApi.getTaskTemplate(id)

    return mapTaskTemplateResponse(response)
  }

  async function createTaskTemplate(payload: ITaskTemplatePayload): Promise<ITaskTemplate> {
    try {
      const response = await taskTemplateApi.createTaskTemplate(payload)
      const template = mapTaskTemplateResponse(response)

      templates.value.unshift(template)
      handleApiSuccess(response)

      return template
    } catch (err: any) {
      handleApiError(err.message ?? 'Не удалось создать шаблон')
      throw err
    }
  }

  async function updateTaskTemplate(
    id: string,
    payload: ITaskTemplatePayload
  ): Promise<ITaskTemplate> {
    try {
      const response = await taskTemplateApi.updateTaskTemplate(id, payload)
      const template = mapTaskTemplateResponse(response)
      const index = templates.value.findIndex(item => item.id === id)

      if (index !== -1) {
        templates.value.splice(index, 1, template)
      }

      handleApiSuccess(response)

      return template
    } catch (err: any) {
      handleApiError(err.message ?? 'Не удалось обновить шаблон')
      throw err
    }
  }

  async function deleteTaskTemplate(id: string): Promise<void> {
    try {
      const response = await taskTemplateApi.deleteTaskTemplate(id)

      templates.value = templates.value.filter(item => item.id !== id)
      handleApiSuccess(response)
    } catch (err: any) {
      handleApiError(err.message ?? 'Не удалось удалить шаблон')
      throw err
    }
  }

  return {
    templates,
    isLoading,
    error,
    getTaskTemplates,
    getTaskTemplate,
    createTaskTemplate,
    updateTaskTemplate,
    deleteTaskTemplate
  }
})
