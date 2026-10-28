<template>
  <div class="tm-calendar-tab">
    <TaskManagerPageSkeleton v-if="loading" />

    <template v-else>
      <div class="tm-calendar-tab__toolbar q-mb-md">
        <p class="tm-calendar-tab__count q-ma-none">
          Напоминаний в месяце: {{ monthEventsCount }}
        </p>
      </div>

      <AppNoResultsPlug
        v-if="!events.length"
        title="Напоминаний пока нет"
        body="Добавьте напоминание в задаче — оно появится в ячейке нужного дня"
      />

      <div
        v-else
        class="tm-calendar-tab__board"
      >
        <AppCalendar
          v-model="selectedDate"
          v-model:view-month="viewMonth"
          :events="events"
          :max-visible-events="4"
          @event-click="onEventClick"
        />

        <aside
          v-if="selectedDate"
          class="tm-calendar-tab__day-panel"
        >
          <h2 class="tm-calendar-tab__day-title">
            {{ selectedDayLabel }}
            <span
              v-if="selectedDayEvents.length"
              class="tm-calendar-tab__day-count"
            >
              {{ selectedDayEvents.length }}
            </span>
          </h2>

          <div
            v-if="selectedDayEvents.length"
            class="tm-calendar-tab__day-items"
          >
            <button
              v-for="item in selectedDayEvents"
              :key="item.id"
              type="button"
              class="tm-calendar-tab__day-item"
              :class="`tm-calendar-tab__day-item--${item.urgency}`"
              @click="openTask(item.taskId)"
            >
              <span class="tm-calendar-tab__day-item-time">
                {{ item.time || '—' }}
              </span>
              <span class="tm-calendar-tab__day-item-title">
                {{ item.title }}
              </span>
            </button>
          </div>

          <p
            v-else
            class="tm-calendar-tab__day-empty"
          >
            На этот день напоминаний нет
          </p>
        </aside>
      </div>
    </template>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import AppCalendar from 'src/components/default/AppCalendar.vue'
import AppNoResultsPlug from 'src/components/default/AppNoResultsPlug.vue'
import TaskManagerPageSkeleton from 'src/pages/client/TaskManager/TaskManagerPageSkeleton.vue'
import { useTaskDialogRoute } from 'src/composables/client/TaskManager/useTaskDialogRoute'
import { useTaskStore } from 'src/stores/modules/taskStore'
import { ICalendarEvent } from 'src/types/calendar'
import { handleApiError } from 'src/utils/jsonapi'
import {
  formatEventTime,
  toDateKey,
  toMonthKey
} from 'src/utils/calendar'
import {
  getReminderUrgency,
  parseApiDatetime,
  ReminderUrgency
} from 'src/utils/reminder'

interface ICalendarReminderEvent extends ICalendarEvent {
  taskId: string
  urgency: ReminderUrgency
}

const URGENCY_COLOR: Record<ReminderUrgency, string> = {
  overdue: '#fecaca',
  'due-soon': '#fde68a',
  upcoming: '#bfdbfe',
  inactive: '#e5e7eb'
}

const taskStore = useTaskStore()
const { openTask } = useTaskDialogRoute()

const loading = ref(true)
const selectedDate = ref<string | null>(toDateKey(new Date()))
const viewMonth = ref(toMonthKey(new Date()))

const events = computed<ICalendarReminderEvent[]>(() => {
  const now = Date.now()
  const items: ICalendarReminderEvent[] = []

  for (const reminderId of taskStore.reminder.allIds) {
    const reminder = taskStore.reminder.byId[reminderId]
    if (!reminder) continue

    const task = taskStore.tasks.byId[reminder.task_id]
    if (!task) continue

    const eventAt = parseApiDatetime(reminder.datetime)
    if (!eventAt) continue

    const urgency = getReminderUrgency(reminder, now)

    items.push({
      id: reminder.id,
      taskId: task.id,
      date: toDateKey(eventAt),
      title: task.title,
      time: formatEventTime(reminder.datetime),
      color: URGENCY_COLOR[urgency],
      urgency
    })
  }

  return items.sort((a, b) => {
    const aKey = `${a.date} ${a.time || '99:99'}`
    const bKey = `${b.date} ${b.time || '99:99'}`
    return aKey.localeCompare(bKey)
  })
})

const monthEventsCount = computed(() =>
  events.value.filter(event => event.date.startsWith(viewMonth.value)).length
)

const selectedDayEvents = computed(() => {
  if (!selectedDate.value) return []
  return events.value.filter(event => event.date === selectedDate.value)
})

const selectedDayLabel = computed(() => {
  if (!selectedDate.value) return ''

  const [year, month, day] = selectedDate.value.split('-').map(Number)
  const date = new Date(year, month - 1, day)

  return new Intl.DateTimeFormat('ru-RU', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: date.getFullYear() === new Date().getFullYear() ? undefined : 'numeric'
  }).format(date)
})

function onEventClick (event: ICalendarEvent) {
  const reminderEvent = events.value.find(item => item.id === event.id)
  if (!reminderEvent) return
  openTask(reminderEvent.taskId)
}

onMounted(async () => {
  try {
    if (!taskStore.taskLists.allIds.length) {
      await taskStore.getTaskLists()
    }
  } catch (error: unknown) {
    handleApiError(error)
  } finally {
    loading.value = false
  }
})
</script>

<style lang="scss" scoped>
.tm-calendar-tab {
  &__toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  &__count {
    color: #6b7280;
    font-size: 14px;
  }

  &__board {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(260px, 320px);
    gap: 16px;
    align-items: start;
  }

  &__day-panel {
    padding: 16px;
    background: #fff;
    border: 1px solid #e4e6ee;
    border-radius: 12px;
    box-shadow: 0 1px 2px rgba(40, 47, 83, 0.04);
  }

  &__day-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 14px;
    color: #1f2439;
    font-size: 15px;
    font-weight: 700;
    line-height: 1.35;
    text-transform: capitalize;
  }

  &__day-count {
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
    font-weight: 700;
    text-transform: none;
  }

  &__day-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  &__day-item {
    display: grid;
    grid-template-columns: 48px minmax(0, 1fr);
    gap: 10px;
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e4e6ee;
    border-radius: 10px;
    border-left-width: 3px;
    background: #fff;
    text-align: left;
    cursor: pointer;
    transition: background-color 0.15s ease, box-shadow 0.15s ease;

    &:hover {
      background: #fafbff;
      box-shadow: 0 4px 14px rgba(40, 47, 83, 0.08);
    }

    &--overdue {
      border-left-color: $negative;
      background: #fdecea;
    }

    &--due-soon {
      border-left-color: $warning;
      background: #fff8e8;
    }

    &--upcoming {
      border-left-color: $primary;
    }

    &--inactive {
      border-left-color: #9e9e9e;
      opacity: 0.88;
    }
  }

  &__day-item-time {
    color: #4b5563;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
  }

  &__day-item-title {
    color: #1f2439;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.35;
    overflow-wrap: anywhere;
  }

  &__day-empty {
    margin: 0;
    color: #6b7280;
    font-size: 13px;
    line-height: 1.45;
  }
}

@media (max-width: 1023px) {
  .tm-calendar-tab {
    &__board {
      grid-template-columns: 1fr;
    }
  }
}
</style>
