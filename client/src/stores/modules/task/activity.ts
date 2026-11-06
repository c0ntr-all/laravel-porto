import { camel } from 'radash'
import { IFilter, IPostAttachment, ITask, ITaskUpdatePayload } from 'src/types'
import { normalizeEntityCollection, upsertEntity, handleApiSuccess, handleApiError } from 'src/utils/jsonapi'
import { useCaseLogApi } from 'src/api/requests/useCaseLogApi'
import { attachmentApi } from 'src/api/requests/attachmentApi'
import { mapResponse } from 'src/utils/jsonApiMapper'
import { TM_TASK_ATTACHABLE_TYPE } from 'src/constants/TaskManager/attachment'
import type { TaskStoreContext } from './collections'

type ActivityContext = TaskStoreContext & {
  updateTask: (id: string, payload: ITaskUpdatePayload) => Promise<boolean>
}

export function createActivityActions(ctx: ActivityContext) {
  const { tasks, attachments } = ctx.state
  const { collections, isValidCollectionKey, upsertMappedAttachments, updateTask } = ctx

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

  async function getUseCaseLogs(filters: IFilter): Promise<void> {
    try {
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
    } catch (error: unknown) {
      handleApiError(error)
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
    getUseCaseLogs,
    uploadTaskAttachments,
    deleteTaskAttachment
  }
}
