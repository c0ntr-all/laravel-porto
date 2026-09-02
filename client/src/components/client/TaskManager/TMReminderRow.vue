<template>
  <article
    class="reminder-row"
    :class="`reminder-row--${urgency}`"
    @click="openTask"
  >
    <div class="reminder-row__body">
      <h3 class="reminder-row__title">{{ task.title }}</h3>
      <div class="reminder-row__meta">
        <span class="reminder-row__date">
          <q-icon name="event" size="14px" />
          {{ humanDatetime(reminder.datetime) }}
        </span>
        <span class="reminder-row__relative">{{ relativeLabel }}</span>
        <q-icon
          v-if="isRecurring"
          name="repeat"
          size="14px"
          class="reminder-row__repeat"
        >
          <q-tooltip>{{ intervalLabel }}</q-tooltip>
        </q-icon>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { IReminderItem, ITask } from 'src/types/TaskManager/task'
import { humanDatetime } from 'src/utils/datetime'
import {
  formatReminderInterval,
  formatReminderRelative,
  getReminderUrgency,
  isRecurringReminder
} from 'src/utils/reminder'

const props = defineProps<{
  task: ITask
  reminder: IReminderItem
}>()

const emit = defineEmits<{
  (e: 'opened', task: ITask): void
}>()

const now = ref(Date.now())
let ticker: ReturnType<typeof setInterval> | null = null

const urgency = computed(() => getReminderUrgency(props.reminder, now.value))
const relativeLabel = computed(() => formatReminderRelative(props.reminder.datetime, now.value))
const isRecurring = computed(() => isRecurringReminder(props.reminder))
const intervalLabel = computed(() => formatReminderInterval(props.reminder.interval))

const openTask = () => emit('opened', props.task)

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
.reminder-row {
  padding: 12px 14px;
  background: #fff;
  border: 1px solid #e4e6ee;
  border-radius: 10px;
  border-left-width: 3px;
  border-left-color: #9e9e9e;
  cursor: pointer;
  transition: background-color 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;

  &:hover {
    background: #fafbff;
    box-shadow: 0 4px 14px rgba(40, 47, 83, 0.08);
  }

  &--upcoming,
  &--inactive {
    border-left-color: #9e9e9e;
  }

  &--inactive {
    background: #f5f5f7;
    opacity: 0.92;
  }

  &--due-soon {
    background: #fff8e8;
    border-color: #f3d48a;
    border-left-color: $warning;
  }

  &--overdue {
    background: #fdecea;
    border-color: #f3c1c0;
    border-left-color: $negative;
  }

  &__title {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    line-height: 1.35;
    color: #1f2439;
    overflow-wrap: anywhere;
  }

  &--inactive &__title {
    color: #6b7280;
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

  &__date {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #1f2439;
    font-weight: 600;
  }

  &--overdue &__relative {
    color: $negative;
    font-weight: 600;
  }

  &--due-soon &__relative {
    color: #b76a00;
    font-weight: 600;
  }

  &__repeat {
    color: #9ca3af;
  }
}
</style>
