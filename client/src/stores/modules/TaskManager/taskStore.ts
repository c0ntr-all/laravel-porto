import { defineStore } from 'pinia'
import { taskApi } from 'src/api/taskApi'
import { ITask } from 'src/types/TaskManager/task'

export const useTaskStore = defineStore('task', {
  state: () => ({
    tasks: [] as ITask[]
  }),

  getters: {
    getTaskById: (state) => (id: number) => {
      return state.tasks.find(task => task.id === id)
    },
    getTasksByStatus: (state) => (status: string) => {
      return state.tasks.filter(task => task.status === status)
    },
    totalTasks: (state) => state.tasks.length
  },

  actions: {
    async updateTask(id: string, payload: object) {
      const updated = await taskApi.updateTask(id, payload)
      this.tasks = this.tasks.map(task => task.id === id ? updated : task)

      return updated
    },

    async updateTaskTitle(id: string, title: string) {
      return this.updateTask(id, { title })
    },

    async updateTaskContent(id: string, content: string) {
      return this.updateTask(id, { content })
    }
  }
})
