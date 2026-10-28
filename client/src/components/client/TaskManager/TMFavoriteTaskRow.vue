<template>
  <article
    class="favorite-row"
    :class="{
      'favorite-row--lifted': isLifted,
      'favorite-row--active': isActive,
      'favorite-row--has-reminder': Boolean(reminder)
    }"
  >
    <q-icon
      class="favorite-row__handle"
      name="drag_indicator"
      @pointerdown="onHandlePointerDown"
    />

    <q-btn
      class="favorite-row__favorite"
      icon="star"
      color="amber-8"
      flat
      round
      dense
      @click.stop="toggleFavorite"
    >
      <q-tooltip>Убрать из избранного</q-tooltip>
    </q-btn>

    <div class="favorite-row__body" @click="openTask">
      <h3 class="favorite-row__title">{{ task.title }}</h3>
      <div class="favorite-row__meta">
        <span
          v-if="listTitle"
          class="favorite-row__meta-item"
        >
          <q-icon name="folder_open" size="14px" />
          {{ listTitle }}
        </span>
        <span
          v-if="reminder"
          class="favorite-row__meta-item"
        >
          <q-icon name="event" size="14px" />
          {{ humanDatetime(reminder.datetime) }}
        </span>
        <span
          v-if="reminder"
          class="favorite-row__relative"
          :class="`favorite-row__relative--${urgency}`"
        >
          {{ relativeLabel }}
        </span>
      </div>
    </div>

    <q-btn
      v-if="reminder && canComplete"
      class="favorite-row__complete"
      icon="done"
      color="grey-7"
      flat
      round
      dense
      :loading="isCompleting"
      @click.stop="completeOccurrence"
    >
      <q-tooltip>
        {{ completeTooltip }}
      </q-tooltip>
    </q-btn>
  </article>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { IReminderItem, ITask } from 'src/types/TaskManager/task'
import { humanDatetime } from 'src/utils/datetime'
import {
  canCompleteReminder,
  formatReminderRelative,
  getReminderUrgency,
  isRecurringReminder
} from 'src/utils/reminder'
import { useTaskStore } from 'src/stores/modules/taskStore'

const props = defineProps<{
  task: ITask
  reminder?: IReminderItem | null
  isLifted?: boolean
  isActive?: boolean
}>()

const emit = defineEmits<{
  (e: 'opened', task: ITask): void
  (e: 'handle-pointerdown', event: PointerEvent): void
}>()

const taskStore = useTaskStore()
const now = ref(Date.now())
const isCompleting = ref(false)
let ticker: ReturnType<typeof setInterval> | null = null

const listTitle = computed(() => {
  if (!props.task.task_list_id) return ''
  return taskStore.taskLists.byId[props.task.task_list_id]?.title ?? ''
})

const urgency = computed(() =>
  props.reminder ? getReminderUrgency(props.reminder, now.value) : 'inactive'
)
const relativeLabel = computed(() =>
  props.reminder ? formatReminderRelative(props.reminder.datetime, now.value) : ''
)
const canComplete = computed(() =>
  props.reminder ? canCompleteReminder(props.reminder) : false
)
const completeTooltip = computed(() =>
  props.reminder && isRecurringReminder(props.reminder)
    ? 'Отметить выполненным — закроет цикл и перенесёт дату'
    : 'Отметить выполненным'
)

const openTask = () => emit('opened', props.task)

const onHandlePointerDown = (event: PointerEvent) => {
  emit('handle-pointerdown', event)
}

async function toggleFavorite () {
  await taskStore.toggleTaskFavorite(props.task.id)
}

async function completeOccurrence () {
  if (!props.reminder || isCompleting.value || !canComplete.value) return

  isCompleting.value = true
  try {
    await taskStore.completeReminder(props.task.id)
  } finally {
    isCompleting.value = false
  }
}

onMounted(() => {
  ticker = setInterval(() => {
    now.value = Date.now()
  }, 30000)
})

onUnmounted(() => {
  if (ticker) clearInterval(ticker)
})
</script>

<style scoped lang="scss">
.favorite-row {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 12px 10px 12px 6px;
  background: #fff;
  border: 1px solid #e4e6ee;
  border-radius: 10px;
  border-left: 3px solid #f59e0b;
  cursor: pointer;
  transition: background-color 0.15s ease, box-shadow 0.15s ease;

  &:hover {
    background: #fffbeb;
    box-shadow: 0 4px 14px rgba(40, 47, 83, 0.08);
  }

  &--lifted {
    position: relative;
    z-index: 3;
    background: #fff;
  }

  &--active {
    box-shadow: 0 14px 28px rgba(40, 47, 83, 0.18);
    cursor: grabbing;
  }

  &__handle {
    flex-shrink: 0;
    color: #9aa0b8;
    cursor: grab;
    touch-action: none;

    &:active {
      cursor: grabbing;
    }
  }

  &__favorite {
    flex-shrink: 0;
  }

  &__body {
    min-width: 0;
    flex: 1;
  }

  &__title {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    line-height: 1.35;
    color: #1f2439;
    overflow-wrap: anywhere;
  }

  &__meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px 12px;
    margin-top: 6px;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.3;
  }

  &__meta-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  &__relative--overdue {
    color: $negative;
    font-weight: 600;
  }

  &__relative--due-soon {
    color: #b76a00;
    font-weight: 600;
  }

  &__complete {
    flex-shrink: 0;
  }
}
</style>
