import { updateObject } from 'src/utils/helpers'
import {
  IReminderCreatePayload,
  IReminderUpdatePayload, IReminderItem, IReminderOccurrence
} from 'src/types'
import {
  upsertEntity, normalizeEntity, normalizeEntityCollection, handleApiSuccess, handleApiError
} from 'src/utils/jsonapi'
import { taskApi } from 'src/api/requests/taskApi'
import type { TaskStoreContext } from './collections'

export function createReminderActions(ctx: TaskStoreContext) {
  const {
    tasks,
    reminder,
    reminderOccurrences
  } = ctx.state

  async function createReminder(
    taskId: string,
    payload: IReminderCreatePayload
  ): Promise<IReminderItem | undefined> {
    try {
      const responseData = await taskApi.createReminder(taskId, payload)
      const { entity } = normalizeEntity<IReminderItem>(responseData.data, responseData.included)

      upsertEntity(reminder, entity)

      const task = tasks.byId[taskId]
      if (task) {
        if (!task.reminderIds) {
          task.reminderIds = []
        }

        if (!task.reminderIds.includes(entity.id)) {
          task.reminderIds.push(entity.id)
        }

        task.reminders_count++
      }

      handleApiSuccess(responseData)

      return entity
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function updateReminder(
    taskId: string,
    payload: IReminderUpdatePayload
  ): Promise<void> {
    try {
      const responseData = await taskApi.updateReminder(taskId, payload)
      const { entity } = normalizeEntity<IReminderItem>(responseData.data, responseData.included)

      const current = reminder.byId[entity.id]
      const updated = current ? updateObject(current, entity) : entity

      upsertEntity(reminder, updated)

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function deleteReminder(taskId: string): Promise<void> {
    try {
      const task = tasks.byId[taskId]
      const reminderId = task?.reminderIds?.[0] ||
        Object.values(reminder.byId).find(item => item.task_id === taskId)?.id

      if (!reminderId) return

      const responseData = await taskApi.deleteReminder(taskId)

      delete reminder.byId[reminderId]
      reminder.allIds = reminder.allIds.filter(id => id !== reminderId)

      reminderOccurrences.allIds = reminderOccurrences.allIds.filter(id => {
        const occurrence = reminderOccurrences.byId[id]
        if (occurrence?.reminder_id === reminderId) {
          delete reminderOccurrences.byId[id]
          return false
        }

        return true
      })

      if (task) {
        task.reminderIds = (task.reminderIds || []).filter(id => id !== reminderId)
        task.reminders_count = Math.max(0, (task.reminders_count || 1) - 1)
      }

      handleApiSuccess(responseData)
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function getReminderOccurrences(taskId: string): Promise<void> {
    try {
      const responseData = await taskApi.getReminderOccurrences(taskId)
      const data = Array.isArray(responseData.data) ? responseData.data : [responseData.data]
      const entities = normalizeEntityCollection(data, responseData.included)

      for (const type in entities) {
        if (type !== 'reminder-occurrences') continue

        for (const id in entities[type]) {
          upsertEntity(reminderOccurrences, entities[type][id] as IReminderOccurrence)
        }
      }
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  async function completeReminder(taskId: string): Promise<IReminderOccurrence | undefined> {
    try {
      const responseData = await taskApi.createReminderOccurrence(taskId, {
        status: 'completed'
      })
      const { entity, related } = normalizeEntity<IReminderOccurrence>(
        responseData.data,
        responseData.included
      )

      upsertEntity(reminderOccurrences, entity)

      const relatedReminders = (related.reminder || related.reminders) as unknown as IReminderItem[] | undefined
      const includedReminders = relatedReminders?.length
        ? relatedReminders
        : (responseData.included || [])
            .filter(item => item.type === 'reminders' || item.type === 'reminder')
            .map(item => ({
              id: item.id,
              ...(item.attributes || {})
            } as IReminderItem))

      for (const item of includedReminders) {
        if (!item.id) continue
        const current = reminder.byId[item.id]
        upsertEntity(reminder, current ? updateObject(current, item) : item)
      }

      handleApiSuccess(responseData)

      return entity
    } catch (error: unknown) {
      handleApiError(error)
    }
  }

  return {
    createReminder,
    updateReminder,
    deleteReminder,
    getReminderOccurrences,
    completeReminder
  }
}
