/** Query key for the task dialog overlay on any Task Manager section. */
export const TASK_DIALOG_QUERY_KEY = 'open-task'

export const TASK_MANAGER_SECTION_ROUTE_NAMES = [
  'task-manager-tasks',
  'task-manager-calendar',
  'task-manager-templates'
] as const

export type TaskManagerSectionRouteName =
  typeof TASK_MANAGER_SECTION_ROUTE_NAMES[number]

export function isTaskManagerSectionRoute (
  name: unknown
): name is TaskManagerSectionRouteName {
  return typeof name === 'string' &&
    (TASK_MANAGER_SECTION_ROUTE_NAMES as readonly string[]).includes(name)
}
