<template>
  <div class="reminders-view">
    <AppNoResultsPlug
      v-if="!groups.length"
      title="Напоминаний пока нет"
      body="Добавьте напоминание в задаче — оно появится здесь в своём списке"
    />

    <section
      v-for="group in groups"
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
import { computed } from 'vue'
import { IReminderItem, ITask } from 'src/types/TaskManager/task'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { getReminderUrgency, parseApiDatetime, ReminderUrgency } from 'src/utils/reminder'
import TMReminderRow from 'src/components/client/TaskManager/TMReminderRow.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import { useTaskDialogRoute } from 'src/composables/client/TaskManager/useTaskDialogRoute'

interface IReminderEntry {
  reminder: IReminderItem
  task: ITask
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

const groups = computed<IReminderGroup[]>(() => {
  const now = Date.now()
  const byList = new Map<string, IReminderEntry[]>()

  for (const reminderId of taskStore.reminder.allIds) {
    const reminder = taskStore.reminder.byId[reminderId]
    if (!reminder) continue

    const task = taskStore.tasks.byId[reminder.task_id]
    if (!task) continue

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

function sortReminderItems(items: IReminderEntry[], now: number): IReminderEntry[] {
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
  }
}
</style>
