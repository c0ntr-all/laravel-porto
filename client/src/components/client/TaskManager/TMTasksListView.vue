<template>
  <div class="tasks-list">
    <AppNoResultsPlug
      v-if="!groups.length"
      title="Задач пока нет"
      body="Создайте карточку в режиме блоков, и она появится здесь"
    />

    <section
      v-for="group in groups"
      :key="group.key"
      class="tasks-list__group"
    >
      <h2 class="tasks-list__group-title">
        {{ group.label }}
        <span class="tasks-list__group-count">{{ group.tasks.length }}</span>
      </h2>

      <div class="tasks-list__items">
        <TMTaskRow
          v-for="task in group.tasks"
          :key="task.id"
          :task="task"
          @opened="openTask"
        />
      </div>
    </section>

    <q-dialog v-model="isDialogOpen" @hide="closeTask">
      <TMTask
        v-if="selectedTaskId !== null"
        :task-id="selectedTaskId"
        @closed="closeTask"
      />
    </q-dialog>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue'
import { ITask } from 'src/types/TaskManager/task'
import { useTaskStore } from 'src/stores/modules/taskStore'
import TMTask from 'src/components/client/TaskManager/TMTask.vue'
import TMTaskRow from 'src/components/client/TaskManager/TMTaskRow.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'

interface ITaskGroup {
  key: string
  label: string
  tasks: ITask[]
}

const taskStore = useTaskStore()
const selectedTaskId = ref<string | null>(null)
const isDialogOpen = ref(false)

const sortedTasks = computed(() =>
  taskStore.tasks.allIds
    .map(id => taskStore.tasks.byId[id])
    .filter((task): task is ITask => Boolean(task))
    .sort((a, b) => parseTaskTime(b.created_at) - parseTaskTime(a.created_at))
)

const groups = computed<ITaskGroup[]>(() => {
  const result: ITaskGroup[] = []

  for (const task of sortedTasks.value) {
    const key = getDateGroupKey(task.created_at)
    const lastGroup = result[result.length - 1]

    if (lastGroup?.key === key) {
      lastGroup.tasks.push(task)
      continue
    }

    result.push({
      key,
      label: getDateGroupLabel(task.created_at),
      tasks: [task]
    })
  }

  return result
})

const openTask = (task: ITask) => {
  selectedTaskId.value = task.id
  isDialogOpen.value = true
}

const closeTask = () => {
  isDialogOpen.value = false
}

function parseTaskDate(value?: string): Date | null {
  if (!value) return null

  const parsed = new Date(value.replace(' ', 'T'))
  return Number.isNaN(parsed.getTime()) ? null : parsed
}

function parseTaskTime(value?: string): number {
  return parseTaskDate(value)?.getTime() ?? 0
}

function getDateGroupKey(value?: string): string {
  const parsed = parseTaskDate(value)
  if (!parsed) return 'unknown'

  return [
    parsed.getFullYear(),
    String(parsed.getMonth() + 1).padStart(2, '0'),
    String(parsed.getDate()).padStart(2, '0')
  ].join('-')
}

function getDateGroupLabel(value?: string): string {
  const parsed = parseTaskDate(value)
  if (!parsed) return 'Без даты'

  const now = new Date()
  const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  const startOfTarget = new Date(parsed.getFullYear(), parsed.getMonth(), parsed.getDate())
  const diffDays = Math.round((startOfToday.getTime() - startOfTarget.getTime()) / 86400000)

  if (diffDays === 0) return 'Сегодня'
  if (diffDays === 1) return 'Вчера'

  return new Intl.DateTimeFormat('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: parsed.getFullYear() === now.getFullYear() ? undefined : 'numeric'
  }).format(parsed)
}
</script>

<style lang="scss" scoped>
.tasks-list {
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
