import { defineStore } from 'pinia'
import { taskApi } from 'src/api/taskApi'
import { ITask, IUpdateTaskResponse } from 'src/types/TaskManager/task'

export const useTaskStore = defineStore('task', {
  state: () => ({
    tasks: [] as ITask[]
  }),

  getters: {
    getTaskById: (state) => (id: string) => {
      return state.tasks.find(task => task.id === id)
    },
    totalTasks: (state) => state.tasks.length
  },

  actions: {
    async updateTask(id: string, payload: object): Promise<IUpdateTaskResponse> {
      const updated = await taskApi.updateTask(id, payload)
      // TODO: Currently, visual updating of parts of the task is based only on the model values, but requires updating in the repository
      // this.tasks = this.tasks.map(task => task.id === id ? updated.data : task)

      return updated
    },

    async updateTaskTitle(id: string, title: string): Promise<IUpdateTaskResponse> {
      return this.updateTask(id, { title })
    },

    async updateTaskContent(id: string, content: string): Promise<IUpdateTaskResponse> {
      return this.updateTask(id, { content })
    }
  }
})
