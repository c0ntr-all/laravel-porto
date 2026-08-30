<template>
  <article
    class="task-row task-item"
    :class="{
      'task-row--finished': isFinished,
      'task-row--declined': isDeclined
    }"
  >
    <q-btn
      class="task-row__status"
      :icon="statusIcon"
      :color="statusColor"
      :disable="isDeclined"
      flat
      round
      dense
      @click.stop="toggleFinish"
    >
      <q-tooltip>
        {{ statusTooltip }}
      </q-tooltip>
    </q-btn>

    <div class="task-row__body" @click="openTask">
      <div class="task-row__headline">
        <h3 class="task-row__title">{{ task.title }}</h3>
        <q-chip
          v-if="listTitle"
          class="task-row__list-tag"
          dense
          outline
          size="sm"
          icon="folder_open"
          color="primary"
          text-color="primary"
        >
          {{ listTitle }}
        </q-chip>
      </div>

      <div class="task-row__meta">
        <span class="task-row__meta-item">
          <q-icon name="event" size="14px" />
          <time>{{ createdAtLabel }}</time>
        </span>

        <span
          v-if="task.checklists_count"
          class="task-row__meta-item"
        >
          <q-icon name="checklist" size="14px" />
          {{ task.checklists_count }}
          <q-tooltip>Чек-листы</q-tooltip>
        </span>

        <span
          v-if="task.reminders_count"
          class="task-row__meta-item"
        >
          <q-icon name="schedule" size="14px" />
          <q-tooltip>Напоминание</q-tooltip>
        </span>

        <span
          v-if="task.progresses_count"
          class="task-row__meta-item"
        >
          <q-icon name="timeline" size="14px" />
          {{ task.progresses_count }}
          <q-tooltip>Прогресс</q-tooltip>
        </span>

        <q-badge
          v-if="isFinished"
          color="positive"
          outline
        >
          Выполнена
        </q-badge>
        <q-badge
          v-else-if="isDeclined"
          color="negative"
          outline
        >
          Отклонена
        </q-badge>
      </div>
    </div>

    <TMTaskListItemActionsButton
      :data="{ isFinished, isDeclined }"
      @finish-switched="switchTaskFinishing"
      @declanation-switched="switchTaskDeclanation"
      @deleted="deleteTask"
    />
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { ITask } from 'src/types/TaskManager/task'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { humanDatetime } from 'src/utils/datetime'
import TMTaskListItemActionsButton from 'src/components/client/TaskManager/TMTaskListItemActionsButton.vue'

const taskStore = useTaskStore()

const props = defineProps<{
  task: ITask
}>()

const emit = defineEmits<{
  (e: 'opened', task: ITask): void
}>()

const isFinished = computed(() => props.task.finished_at !== null)
const isDeclined = computed(() => props.task.is_declined)

const listTitle = computed(() =>
  taskStore.taskLists.byId[props.task.task_list_id]?.title ?? ''
)

const createdAtLabel = computed(() =>
  props.task.created_at ? humanDatetime(props.task.created_at) : 'Без даты'
)

const statusIcon = computed(() => {
  if (isDeclined.value) return 'cancel'
  if (isFinished.value) return 'check_circle'
  return 'radio_button_unchecked'
})

const statusColor = computed(() => {
  if (isDeclined.value) return 'negative'
  if (isFinished.value) return 'positive'
  return 'grey-6'
})

const statusTooltip = computed(() => {
  if (isDeclined.value) return 'Задача отклонена'
  if (isFinished.value) return 'Вернуть в работу'
  return 'Отметить выполненной'
})

const openTask = () => emit('opened', props.task)

async function switchTaskFinishing(status: boolean) {
  await taskStore.updateTask(props.task.id, { is_finished: status })
}

async function switchTaskDeclanation(status: boolean) {
  await taskStore.updateTask(props.task.id, { is_declined: status })
}

async function deleteTask() {
  await taskStore.deleteTask(props.task.id)
}

async function toggleFinish() {
  if (isDeclined.value) return
  await switchTaskFinishing(!isFinished.value)
}
</script>

<style scoped lang="scss">
.task-row {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 12px 40px 12px 8px;
  background: #fff;
  border: 1px solid #e4e6ee;
  border-radius: 10px;
  border-left: 3px solid $primary;
  box-shadow: 0 1px 2px rgba(40, 47, 83, 0.04);
  transition: background-color 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;

  &:hover {
    background: #fafbff;
    border-color: #d5d8e4;
    box-shadow: 0 4px 14px rgba(40, 47, 83, 0.08);
  }

  &--finished {
    border-left-color: $positive;

    .task-row__title {
      color: #6b7280;
      text-decoration: line-through;
    }
  }

  &--declined {
    border-left-color: $negative;
    opacity: 0.88;
  }

  &__status {
    margin-top: 1px;
    flex-shrink: 0;
  }

  &__body {
    min-width: 0;
    flex: 1;
    cursor: pointer;
  }

  &__headline {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }

  &__title {
    margin: 0;
    min-width: 0;
    font-size: 15px;
    font-weight: 600;
    line-height: 1.35;
    color: #1f2439;
    overflow-wrap: anywhere;
  }

  &__list-tag {
    max-width: 180px;
    flex-shrink: 0;
    margin: 0;

    :deep(.q-chip__content) {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  }

  &__meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px 12px;
    margin-top: 8px;
    color: #6b7280;
    font-size: 12px;
    line-height: 1;
  }

  &__meta-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
}

@media (max-width: 599px) {
  .task-row {
    &__headline {
      flex-direction: column;
      align-items: flex-start;
      gap: 6px;
    }

    &__list-tag {
      max-width: 100%;
    }
  }
}
</style>
