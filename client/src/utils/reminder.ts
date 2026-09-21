import {
  IReminderCreatePayload,
  IReminderDuration,
  IReminderFormModel,
  IReminderItem,
  ReminderBeforeUnit,
  ReminderIntervalUnit,
  ReminderTimeUnit
} from 'src/types/TaskManager/task'
import { getCurrentDateTime } from 'src/utils/datetime'
import {
  ReminderBeforeUnitEnum,
  ReminderIntervalUnitEnum
} from 'src/enums/TaskManager/ReminderTimeUnitEnum'

export type ReminderUrgency = 'overdue' | 'due-soon' | 'upcoming' | 'inactive'

const MS_IN_DAY = 24 * 60 * 60 * 1000

const UNIT_FORMS: Record<ReminderTimeUnit, [string, string, string]> = {
  minute: ['минуту', 'минуты', 'минут'],
  hour: ['час', 'часа', 'часов'],
  day: ['день', 'дня', 'дней'],
  week: ['неделю', 'недели', 'недель'],
  month: ['месяц', 'месяца', 'месяцев'],
  year: ['год', 'года', 'лет']
}

const UNIT_EACH: Record<ReminderTimeUnit, string> = {
  minute: 'Каждую минуту',
  hour: 'Каждый час',
  day: 'Каждый день',
  week: 'Каждую неделю',
  month: 'Каждый месяц',
  year: 'Каждый год'
}

export function parseApiDatetime(value?: string | null): Date | null {
  if (!value) return null

  const parsed = new Date(value.replace(' ', 'T'))
  return Number.isNaN(parsed.getTime()) ? null : parsed
}

export function toReminderDatetime(value: string): string {
  return value.slice(0, 16)
}

export function getReminderUrgency(reminder: IReminderItem, now = Date.now()): ReminderUrgency {
  if (!reminder.is_active) return 'inactive'

  const eventAt = parseApiDatetime(reminder.datetime)
  if (!eventAt) return 'upcoming'

  const diff = eventAt.getTime() - now
  if (diff < 0) return 'overdue'
  if (diff <= MS_IN_DAY) return 'due-soon'
  return 'upcoming'
}

export function formatReminderRelative(datetime: string, now = Date.now()): string {
  const eventAt = parseApiDatetime(datetime)
  if (!eventAt) return ''

  const diff = eventAt.getTime() - now
  const duration = formatMsDuration(Math.abs(diff))

  if (diff < 0) {
    return duration === 'меньше минуты'
      ? 'Просрочено'
      : `Просрочено на ${duration}`
  }

  if (duration === 'меньше минуты') return 'Сейчас'
  if (diff <= MS_IN_DAY) return `Осталось ${duration}`
  return `Через ${duration}`
}

export function formatReminderInterval(interval: IReminderDuration | null | undefined): string | null {
  if (!interval?.value || !interval.unit) return null
  if (interval.value === 1) return UNIT_EACH[interval.unit]
  return `Каждые ${formatDuration(interval.value, interval.unit)}`
}

export function formatRemindBefore(offset: IReminderDuration | null | undefined): string | null {
  if (!offset?.value || !offset.unit) return null
  return `За ${formatDuration(offset.value, offset.unit)} до события`
}

export function isRecurringReminder(reminder: IReminderItem): boolean {
  return Boolean(reminder.interval?.value && reminder.interval.unit)
}

export function canCompleteReminder(reminder: IReminderItem): boolean {
  if (typeof reminder.can_complete === 'boolean') {
    return reminder.can_complete
  }

  return reminder.is_active && Boolean(reminder.datetime)
}

export function formatDuration(value: number, unit: ReminderTimeUnit): string {
  return `${value} ${pluralizeRu(value, UNIT_FORMS[unit])}`
}

export function createReminderFormModel(): IReminderFormModel {
  return {
    datetime: getCurrentDateTime(),
    is_repeating: false,
    interval_value: 1,
    interval_unit: ReminderIntervalUnitEnum.DAY,
    is_to_remind_before: false,
    remind_before_value: 30,
    remind_before_unit: ReminderBeforeUnitEnum.MINUTE,
    is_active: true
  }
}

export function reminderToFormModel(reminder: IReminderItem): IReminderFormModel {
  return {
    datetime: toReminderDatetime(reminder.datetime),
    is_repeating: Boolean(reminder.interval?.value && reminder.interval.unit),
    interval_value: reminder.interval?.value || 1,
    interval_unit: (reminder.interval?.unit as ReminderIntervalUnit) || ReminderIntervalUnitEnum.DAY,
    is_to_remind_before: Boolean(reminder.to_remind_before?.value && reminder.to_remind_before.unit),
    remind_before_value: reminder.to_remind_before?.value || 30,
    remind_before_unit: (reminder.to_remind_before?.unit as ReminderBeforeUnit) || ReminderBeforeUnitEnum.MINUTE,
    is_active: reminder.is_active
  }
}

export function toReminderPayload(model: IReminderFormModel): IReminderCreatePayload {
  const payload: IReminderCreatePayload = {
    datetime: toReminderDatetime(model.datetime),
    is_active: model.is_active
  }

  payload.interval = model.is_repeating
    ? {
        value: Number(model.interval_value),
        unit: model.interval_unit
      }
    : null

  payload.to_remind_before = model.is_to_remind_before
    ? {
        value: Number(model.remind_before_value),
        unit: model.remind_before_unit
      }
    : null

  return payload
}

export function isReminderPayloadValid(payload: IReminderCreatePayload): boolean {
  if (!payload.datetime) return false
  if (payload.interval && (!payload.interval.value || payload.interval.value < 1)) return false
  if (payload.to_remind_before && (!payload.to_remind_before.value || payload.to_remind_before.value < 1)) {
    return false
  }

  return true
}

function formatMsDuration(ms: number): string {
  const minutes = Math.round(ms / 60000)
  if (minutes < 1) return 'меньше минуты'
  if (minutes < 60) return formatDuration(minutes, 'minute')

  const hours = Math.round(minutes / 60)
  if (hours < 24) return formatDuration(hours, 'hour')

  const days = Math.round(hours / 24)
  if (days < 7) return formatDuration(days, 'day')

  const weeks = Math.round(days / 7)
  if (weeks < 5) return formatDuration(weeks, 'week')

  const months = Math.round(days / 30)
  if (months < 12) return formatDuration(months, 'month')

  return formatDuration(Math.round(days / 365), 'year')
}

function pluralizeRu(value: number, forms: [string, string, string]): string {
  const abs = Math.abs(value) % 100
  const last = abs % 10

  if (abs > 10 && abs < 20) return forms[2]
  if (last === 1) return forms[0]
  if (last >= 2 && last <= 4) return forms[1]
  return forms[2]
}
