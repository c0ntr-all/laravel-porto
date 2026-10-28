import { computed, nextTick } from 'vue'
import { LocationQuery, LocationQueryRaw, useRoute, useRouter } from 'vue-router'
import { ITask } from 'src/types/TaskManager/task'
import {
  isTaskManagerSectionRoute,
  TASK_DIALOG_QUERY_KEY
} from 'src/constants/TaskManager/route'

// Shared across composable instances: q-dialog hide during in-dialog
// navigation must be ignored by the page-level closeTask handler.
let ignoreDialogClose = false
let ignoreDialogCloseTimer: ReturnType<typeof setTimeout> | null = null

function armIgnoreDialogClose () {
  ignoreDialogClose = true
  if (ignoreDialogCloseTimer) {
    clearTimeout(ignoreDialogCloseTimer)
  }
  ignoreDialogCloseTimer = setTimeout(() => {
    ignoreDialogClose = false
    ignoreDialogCloseTimer = null
  }, 100)
}

function readQueryValue (value: LocationQuery[string]): string | null {
  if (Array.isArray(value)) {
    return value[0] || null
  }

  return value || null
}

function withTaskQuery (query: LocationQuery, taskId: string): LocationQueryRaw {
  return {
    ...query,
    [TASK_DIALOG_QUERY_KEY]: taskId
  }
}

function withoutTaskQuery (query: LocationQuery): LocationQueryRaw {
  const next: LocationQueryRaw = { ...query }
  delete next[TASK_DIALOG_QUERY_KEY]
  return next
}

/**
 * Opens/closes the task modal via `?task=<id>` on the current section route.
 * Keeps calendar/templates/tasks background intact and scales to new sections.
 */
export function useTaskDialogRoute () {
  const route = useRoute()
  const router = useRouter()

  const selectedTaskId = computed(() => {
    if (!isTaskManagerSectionRoute(route.name)) return null

    return readQueryValue(route.query[TASK_DIALOG_QUERY_KEY])
  })

  const isDialogOpen = computed({
    get: () => selectedTaskId.value !== null,
    set: (open: boolean) => {
      if (!open) {
        closeTask()
      }
    }
  })

  async function openTask (task: ITask | string) {
    const taskId = typeof task === 'string' ? task : task.id
    if (!taskId || taskId === selectedTaskId.value) return
    if (!isTaskManagerSectionRoute(route.name)) return

    const location = {
      name: route.name,
      params: route.params,
      query: withTaskQuery(route.query, taskId)
    }

    if (selectedTaskId.value) {
      armIgnoreDialogClose()
      await router.replace(location)
      await nextTick()
      return
    }

    void router.push(location)
  }

  function closeTask () {
    if (ignoreDialogClose) return
    if (!selectedTaskId.value) return
    if (!isTaskManagerSectionRoute(route.name)) return

    const previous = window.history.state?.back
    const previousUrl = typeof previous === 'string' ? previous : null
    const previousHadTask = Boolean(
      previousUrl && new URL(previousUrl, window.location.origin).searchParams.has(TASK_DIALOG_QUERY_KEY)
    )

    // Prefer history back when we opened the dialog with push() from the same section
    if (
      previousUrl &&
      previousUrl.startsWith('/task-manager') &&
      !previousHadTask
    ) {
      router.back()
      return
    }

    void router.replace({
      name: route.name,
      params: route.params,
      query: withoutTaskQuery(route.query)
    })
  }

  return {
    selectedTaskId,
    isDialogOpen,
    openTask,
    closeTask
  }
}
