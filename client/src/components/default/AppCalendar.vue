<template>
  <div class="app-calendar">
    <header class="app-calendar__toolbar">
      <div class="app-calendar__nav">
        <q-btn
          icon="chevron_left"
          flat
          round
          dense
          aria-label="Предыдущий месяц"
          @click="shiftViewMonth(-1)"
        />

        <q-select
          v-model="selectedMonth"
          class="app-calendar__month-select"
          :options="monthOptions"
          emit-value
          map-options
          dense
          outlined
          options-dense
          hide-bottom-space
        />

        <q-select
          v-model="selectedYear"
          class="app-calendar__year-select"
          :options="yearOptions"
          dense
          outlined
          options-dense
          hide-bottom-space
        />

        <q-btn
          icon="chevron_right"
          flat
          round
          dense
          aria-label="Следующий месяц"
          @click="shiftViewMonth(1)"
        />
      </div>

      <q-btn
        label="Сегодня"
        color="primary"
        outline
        dense
        no-caps
        @click="goToToday"
      />
    </header>

    <div class="app-calendar__weekdays" aria-hidden="true">
      <span
        v-for="label in weekdayLabels"
        :key="label"
        class="app-calendar__weekday"
      >
        {{ label }}
      </span>
    </div>

    <div
      class="app-calendar__grid"
      role="grid"
      :aria-label="ariaMonthLabel"
    >
      <div
        v-for="cell in cells"
        :key="cell.date"
        class="app-calendar__day"
        :class="{
          'app-calendar__day--outside': !cell.inCurrentMonth,
          'app-calendar__day--today': cell.isToday,
          'app-calendar__day--selected': cell.isSelected
        }"
        role="gridcell"
        @click="selectDay(cell.date)"
      >
        <div class="app-calendar__day-header">
          <span class="app-calendar__day-number">{{ cell.day }}</span>
        </div>

        <div class="app-calendar__day-events">
          <slot name="day" :cell="cell">
            <button
              v-for="event in visibleEvents(cell)"
              :key="event.id"
              type="button"
              class="app-calendar__event"
              :title="eventTooltip(event)"
              @click.stop="onEventClick(event)"
            >
              <span
                class="app-calendar__event-stripe"
                :style="stripeStyle(event)"
                aria-hidden="true"
              />
              <span class="app-calendar__event-time">
                {{ event.time || '—' }}
              </span>
            </button>

            <button
              v-if="hiddenEventsCount(cell) > 0"
              type="button"
              class="app-calendar__more"
              @click.stop="selectDay(cell.date)"
            >
              ещё {{ hiddenEventsCount(cell) }}
            </button>
          </slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import {
  CalendarWeekStart,
  ICalendarDayCell,
  ICalendarEvent
} from 'src/types/calendar'
import {
  buildMonthGrid,
  getMonthOptions,
  getWeekdayLabels,
  getYearOptions,
  parseMonthKey,
  shiftMonth,
  toDateKey,
  toMonthKey
} from 'src/utils/calendar'

const props = withDefaults(defineProps<{
  modelValue?: string | null
  viewMonth?: string
  events?: ICalendarEvent[]
  locale?: string
  weekStartsOn?: CalendarWeekStart
  maxVisibleEvents?: number
}>(), {
  modelValue: null,
  viewMonth: undefined,
  events: () => [],
  locale: 'ru-RU',
  weekStartsOn: 1,
  maxVisibleEvents: 3
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | null): void
  (e: 'update:viewMonth', value: string): void
  (e: 'day-click', value: string): void
  (e: 'event-click', value: ICalendarEvent): void
}>()

const resolvedViewMonth = computed(() => {
  if (props.viewMonth) return props.viewMonth
  if (props.modelValue) return props.modelValue.slice(0, 7)
  return toMonthKey(new Date())
})

const monthOptions = computed(() => getMonthOptions(props.locale))
const yearOptions = computed(() => {
  const parsed = parseMonthKey(resolvedViewMonth.value)
  const centerYear = parsed?.getFullYear() ?? new Date().getFullYear()
  return getYearOptions(centerYear)
})

const selectedMonth = computed({
  get: () => {
    const parsed = parseMonthKey(resolvedViewMonth.value)
    return (parsed?.getMonth() ?? 0) + 1
  },
  set: (month: number) => {
    const parsed = parseMonthKey(resolvedViewMonth.value) ?? new Date()
    const next = new Date(parsed.getFullYear(), month - 1, 1)
    emit('update:viewMonth', toMonthKey(next))
  }
})

const selectedYear = computed({
  get: () => {
    const parsed = parseMonthKey(resolvedViewMonth.value)
    return parsed?.getFullYear() ?? new Date().getFullYear()
  },
  set: (year: number) => {
    const parsed = parseMonthKey(resolvedViewMonth.value) ?? new Date()
    const next = new Date(year, parsed.getMonth(), 1)
    emit('update:viewMonth', toMonthKey(next))
  }
})

const weekdayLabels = computed(() => getWeekdayLabels(props.weekStartsOn, props.locale))

const ariaMonthLabel = computed(() => {
  const parsed = parseMonthKey(resolvedViewMonth.value)
  if (!parsed) return resolvedViewMonth.value

  return new Intl.DateTimeFormat(props.locale, {
    month: 'long',
    year: 'numeric'
  }).format(parsed)
})

const cells = computed(() => buildMonthGrid({
  monthKey: resolvedViewMonth.value,
  selectedDate: props.modelValue,
  events: props.events,
  weekStartsOn: props.weekStartsOn
}))

function visibleEvents (cell: ICalendarDayCell): ICalendarEvent[] {
  return cell.events.slice(0, props.maxVisibleEvents)
}

function hiddenEventsCount (cell: ICalendarDayCell): number {
  return Math.max(0, cell.events.length - props.maxVisibleEvents)
}

function stripeStyle (event: ICalendarEvent): Record<string, string> | undefined {
  if (!event.color) return undefined

  return {
    backgroundColor: event.color
  }
}

function eventTooltip (event: ICalendarEvent): string {
  return event.time ? `${event.time} ${event.title}` : event.title
}

function selectDay (date: string) {
  emit('update:modelValue', date)
  emit('day-click', date)

  const monthKey = date.slice(0, 7)
  if (monthKey !== resolvedViewMonth.value) {
    emit('update:viewMonth', monthKey)
  }
}

function onEventClick (event: ICalendarEvent) {
  emit('event-click', event)
  emit('update:modelValue', event.date)
}

function shiftViewMonth (delta: number) {
  emit('update:viewMonth', shiftMonth(resolvedViewMonth.value, delta))
}

function goToToday () {
  const today = toDateKey(new Date())
  emit('update:modelValue', today)
  emit('update:viewMonth', today.slice(0, 7))
  emit('day-click', today)
}
</script>

<style lang="scss" scoped>
.app-calendar {
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-width: 0;

  &__toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  &__nav {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    flex-wrap: wrap;
  }

  &__month-select {
    width: 140px;
  }

  &__year-select {
    width: 100px;
  }

  &__weekdays,
  &__grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
  }

  &__weekdays {
    gap: 1px;
  }

  &__weekday {
    padding: 6px 4px;
    text-align: center;
    color: #6b7280;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  &__grid {
    gap: 1px;
    overflow: hidden;
    border: 1px solid #e4e6ee;
    border-radius: 12px;
    background: #e4e6ee;
  }

  &__day {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-height: 90px;
    padding: 6px;
    background: #fff;
    cursor: pointer;
    transition: background-color 0.15s ease;

    &:hover {
      background: #fafbff;
    }

    &--outside {
      background: #f7f7fa;
      color: #9ca3af;

      &:hover {
        background: #f0f0f5;
      }

      .app-calendar__event {
        opacity: 0.72;
      }
    }

    &--today {
      .app-calendar__day-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 999px;
        background: $primary;
        color: #fff;
      }
    }

    &--selected:not(.app-calendar__day--today) {
      background: rgba(25, 118, 210, 0.06);
      box-shadow: inset 0 0 0 1px rgba(25, 118, 210, 0.28);
    }
  }

  &__day-header {
    display: flex;
    justify-content: flex-end;
  }

  &__day-number {
    font-size: 12px;
    font-weight: 700;
    line-height: 24px;
    color: inherit;
  }

  &__day-events {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    min-height: 0;
  }

  &__event {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    max-width: 100%;
    padding: 0;
    border: 0;
    background: transparent;
    color: #374151;
    text-align: left;
    cursor: pointer;

    &:hover .app-calendar__event-time {
      color: #111827;
    }

    &:focus-visible {
      outline: 2px solid $primary;
      outline-offset: 2px;
      border-radius: 4px;
    }
  }

  &__event-stripe {
    flex-shrink: 0;
    width: 18px;
    height: 4px;
    border-radius: 999px;
    background: $primary;
  }

  &__event-time {
    min-width: 0;
    font-size: 11px;
    font-weight: 600;
    line-height: 1;
    letter-spacing: 0.01em;
    font-variant-numeric: tabular-nums;
  }

  &__more {
    padding: 0;
    border: 0;
    background: transparent;
    color: #6b7280;
    font-size: 11px;
    font-weight: 600;
    line-height: 1;
    cursor: pointer;

    &:hover {
      color: $primary;
      text-decoration: underline;
    }
  }
}

@media (max-width: 899px) {
  .app-calendar {
    &__day {
      min-height: 168px;
      padding: 4px;
    }
  }
}

@media (max-width: 599px) {
  .app-calendar {
    &__month-select {
      width: 120px;
    }

    &__year-select {
      width: 88px;
    }

    &__day {
      min-height: 147px;
    }

    &__event-stripe {
      width: 14px;
    }

    &__event-time {
      font-size: 10px;
    }
  }
}
</style>
