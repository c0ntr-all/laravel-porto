import { defineStore } from 'pinia'
import { createActivityActions } from './task/activity'
import { createChecklistActions } from './task/checklists'
import { createTaskCollections } from './task/collections'
import { createCommentActions } from './task/comments'
import { createReminderActions } from './task/reminders'
import { createTaskListActions } from './task/taskLists'
import { createTaskActions } from './task/tasks'

export const useTaskStore = defineStore('task', () => {
  const collections = createTaskCollections()
  const lists = createTaskListActions(collections)
  const tasks = createTaskActions(collections)
  const checklists = createChecklistActions(collections)
  const reminders = createReminderActions(collections)
  const comments = createCommentActions(collections)
  const activity = createActivityActions({
    ...collections,
    updateTask: tasks.updateTask
  })

  return {
    ...collections.state,
    ...lists,
    ...tasks,
    ...checklists,
    ...reminders,
    ...comments,
    ...activity
  }
})
