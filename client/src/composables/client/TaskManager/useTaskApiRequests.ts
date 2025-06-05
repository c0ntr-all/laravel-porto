import { ref } from 'vue'
import { api } from 'src/boot/axios'
import { AxiosError } from 'axios'
import {
  ITask,
  IDeclineTaskResponse,
  IDeleteTaskResponse,
  IUpdateTaskResponse
} from 'src/components/client/TaskManager/types'
import { handleApiError, handleApiSuccess } from 'src/utils/jsonapi'

export function useTaskApiRequests(initialTask: ITask) {
  const task = ref({ ...initialTask })

  const switchTaskFinishing = async (status: boolean) => {
    try {
      const response = await api.patch<IUpdateTaskResponse>(`v1/task-manager/tasks/${task.value.id}`, {
        is_finished: status
      })
      task.value.finished_at = response.data.data.attributes.finished_at
      handleApiSuccess(response)
    } catch (error) {
      handleApiError(error as AxiosError)
    }
  }

  const switchTaskDeclanation = async (status: boolean) => {
    try {
      const response = await api.patch<IDeclineTaskResponse>(`v1/task-manager/tasks/${task.value.id}`, {
        is_declined: status
      })
      task.value.is_declined = response.data.data.attributes.is_declined
      handleApiSuccess(response)
    } catch (error) {
      handleApiError(error as AxiosError)
    }
  }

  const deleteTask = async () => {
    try {
      const response = await api.delete<IDeleteTaskResponse>(`v1/task-manager/tasks/${task.value.id}`)
      handleApiSuccess(response)
    } catch (error) {
      handleApiError(error as AxiosError)
    }
  }

  return {
    task,
    switchTaskFinishing,
    switchTaskDeclanation,
    deleteTask
  }
}
