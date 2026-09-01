import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ITask } from 'src/types/TaskManager/task'

export function useTaskDialogRoute() {
  const route = useRoute()
  const router = useRouter()

  const selectedTaskId = computed(() => {
    const param = route.params.taskId
    const taskId = Array.isArray(param) ? param[0] : param

    return taskId || null
  })

  const isDialogOpen = computed({
    get: () => selectedTaskId.value !== null,
    set: (open: boolean) => {
      if (!open) {
        closeTask()
      }
    }
  })

  function openTask(task: ITask | string) {
    const taskId = typeof task === 'string' ? task : task.id
    if (!taskId || taskId === selectedTaskId.value) return

    const location = {
      name: 'task-manager-task',
      params: { taskId }
    }

    if (route.name === 'task-manager-task') {
      void router.replace(location)
      return
    }

    void router.push(location)
  }

  function closeTask() {
    if (route.name !== 'task-manager-task') return

    const previous = window.history.state?.back
    if (previous === '/task-manager' || previous === '/task-manager/') {
      router.back()
      return
    }

    void router.replace({ name: 'task-manager' })
  }

  return {
    selectedTaskId,
    isDialogOpen,
    openTask,
    closeTask
  }
}
