<template>
  <div class="reminders-view">
    <AppNoResultsPlug
      v-if="!hasContent"
      title="Напоминаний пока нет"
      body="Добавьте напоминание в задаче — оно появится здесь в своём списке"
    />

    <section
      v-if="favoriteItems.length"
      class="reminders-view__group"
    >
      <h2 class="reminders-view__group-title reminders-view__group-title--favorite">
        <q-icon name="star" size="16px" color="amber-8" />
        Избранное
        <span class="reminders-view__group-count">{{ favoriteItems.length }}</span>
      </h2>

      <div
        ref="favoritesListRef"
        class="reminders-view__items"
        :class="{ 'reminders-view__items--sorting': isSorting }"
      >
        <TMFavoriteTaskRow
          v-for="(item, index) in displayFavorites"
          :key="item.id"
          class="reminders-view__favorite-row"
          :task="item.task"
          :reminder="item.reminder"
          :is-lifted="draggingId === item.id"
          :is-active="draggingId === item.id && dragActive"
          :style="rowStyle(index)"
          @opened="openTask"
          @handle-pointerdown="onPointerDown($event, item.id, index)"
        />
      </div>
    </section>

    <section
      v-for="group in reminderGroups"
      :key="group.key"
      class="reminders-view__group"
    >
      <h2 class="reminders-view__group-title">
        <q-icon name="folder_open" size="16px" />
        {{ group.label }}
        <span class="reminders-view__group-count">{{ group.items.length }}</span>
      </h2>

      <div class="reminders-view__items">
        <TMReminderRow
          v-for="item in group.items"
          :key="item.reminder.id"
          :task="item.task"
          :reminder="item.reminder"
          @opened="openTask"
        />
      </div>
    </section>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { IReminderItem, ITask } from 'src/types/TaskManager/task'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { getReminderUrgency, parseApiDatetime, ReminderUrgency } from 'src/utils/reminder'
import TMReminderRow from 'src/components/client/TaskManager/TMReminderRow.vue'
import TMFavoriteTaskRow from 'src/components/client/TaskManager/TMFavoriteTaskRow.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import { useTaskDialogRoute } from 'src/composables/client/TaskManager/useTaskDialogRoute'
import { useVerticalReorder } from 'src/composables/client/TaskManager/useVerticalReorder'

interface IReminderEntry {
  reminder: IReminderItem
  task: ITask
}

interface IFavoriteEntry {
  id: string
  task: ITask
  reminder: IReminderItem | null
}

interface IReminderGroup {
  key: string
  label: string
  items: IReminderEntry[]
}

const URGENCY_ORDER: Record<ReminderUrgency, number> = {
  overdue: 0,
  'due-soon': 1,
  upcoming: 2,
  inactive: 3
}

const taskStore = useTaskStore()
const { openTask } = useTaskDialogRoute()
const favoritesListRef = ref<HTMLElement | null>(null)

const favoriteItems = computed<IFavoriteEntry[]>(() =>
  taskStore.tasks.allIds
    .map(id => taskStore.tasks.byId[id])
    .filter((task): task is ITask => Boolean(task?.is_favorite))
    .sort((a, b) => (a.priority ?? 0) - (b.priority ?? 0) || a.id.localeCompare(b.id))
    .map(task => ({
      id: task.id,
      task,
      reminder: findTaskReminder(task.id)
    }))
)

const favoriteTaskIds = computed(() => new Set(favoriteItems.value.map(item => item.id)))

const {
  displayItems: displayFavorites,
  isSorting,
  draggingId,
  dragActive,
  rowStyle,
  onPointerDown
} = useVerticalReorder(favoritesListRef, {
  items: favoriteItems,
  rowSelector: '.reminders-view__favorite-row',
  onReorder: async (orderedIds) => {
    await taskStore.reorderTasks(orderedIds)
  }
})

const reminderGroups = computed<IReminderGroup[]>(() => {
  const now = Date.now()
  const byList = new Map<string, IReminderEntry[]>()

  for (const reminderId of taskStore.reminder.allIds) {
    const reminder = taskStore.reminder.byId[reminderId]
    if (!reminder) continue

    const task = taskStore.tasks.byId[reminder.task_id]
    if (!task) continue
    if (favoriteTaskIds.value.has(task.id)) continue

    const listId = task.task_list_id || 'ungrouped'
    const bucket = byList.get(listId) ?? []
    bucket.push({ reminder, task })
    byList.set(listId, bucket)
  }

  const result: IReminderGroup[] = []

  for (const listId of taskStore.taskLists.allIds) {
    const items = byList.get(listId)
    if (!items?.length) continue

    result.push({
      key: listId,
      label: taskStore.taskLists.byId[listId]?.title || 'Без списка',
      items: sortReminderItems(items, now)
    })
  }

  const ungrouped = byList.get('ungrouped')
  if (ungrouped?.length) {
    result.push({
      key: 'ungrouped',
      label: 'Без списка',
      items: sortReminderItems(ungrouped, now)
    })
  }

  return result
})

const hasContent = computed(() =>
  favoriteItems.value.length > 0 || reminderGroups.value.length > 0
)

function findTaskReminder (taskId: string): IReminderItem | null {
  return Object.values(taskStore.reminder.byId).find(item => item.task_id === taskId) || null
}

function sortReminderItems (items: IReminderEntry[], now: number): IReminderEntry[] {
  return [...items].sort((a, b) => {
    const urgencyDiff = URGENCY_ORDER[getReminderUrgency(a.reminder, now)] -
      URGENCY_ORDER[getReminderUrgency(b.reminder, now)]
    if (urgencyDiff !== 0) return urgencyDiff

    const aTime = parseApiDatetime(a.reminder.datetime)?.getTime() ?? 0
    const bTime = parseApiDatetime(b.reminder.datetime)?.getTime() ?? 0
    return aTime - bTime
  })
}
</script>

<style lang="scss" scoped>
.reminders-view {
  max-width: 860px;

  &__group {
    & + & {
      margin-top: 8px;
    }
  }

  &__group-title {
    position: sticky;
    top: 0;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 10px;
    padding: 8px 2px;
    background: #f0f0f5;
    color: #6b7280;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;
    line-height: 1;
    text-transform: uppercase;

    &--favorite {
      color: #b45309;
    }
  }

  &__group-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: #e4e6ee;
    color: #4b5563;
    font-size: 11px;
    letter-spacing: 0;
  }

  &__items {
    display: flex;
    flex-direction: column;
    gap: 8px;

    &--sorting {
      user-select: none;
      touch-action: none;
      cursor: grabbing;
    }
  }
}
</style>
