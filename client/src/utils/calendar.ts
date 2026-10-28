import { CalendarWeekStart, ICalendarDayCell, ICalendarEvent } from 'src/types/calendar'

const DATE_KEY_RE = /^\d{4}-\d{2}-\d{2}$/

export function toDateKey (value: Date): string {
  const year = value.getFullYear()
  const month = String(value.getMonth() + 1).padStart(2, '0')
  const day = String(value.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

export function parseDateKey (value: string): Date | null {
  if (!DATE_KEY_RE.test(value)) return null

  const [year, month, day] = value.split('-').map(Number)
  const parsed = new Date(year, month - 1, day)

  if (
    parsed.getFullYear() !== year ||
    parsed.getMonth() !== month - 1 ||
    parsed.getDate() !== day
  ) {
    return null
  }

  return parsed
}

export function toMonthKey (value: Date): string {
  const year = value.getFullYear()
  const month = String(value.getMonth() + 1).padStart(2, '0')

  return `${year}-${month}`
}

export function parseMonthKey (value: string): Date | null {
  const match = value.match(/^(\d{4})-(\d{2})$/)
  if (!match) return null

  const year = Number(match[1])
  const month = Number(match[2])
  if (month < 1 || month > 12) return null

  return new Date(year, month - 1, 1)
}

export function shiftMonth (monthKey: string, delta: number): string {
  const base = parseMonthKey(monthKey) ?? new Date()
  const next = new Date(base.getFullYear(), base.getMonth() + delta, 1)

  return toMonthKey(next)
}

export function getMonthOptions (locale = 'ru-RU'): Array<{ label: string, value: number }> {
  const formatter = new Intl.DateTimeFormat(locale, { month: 'long' })

  return Array.from({ length: 12 }, (_, index) => {
    const label = formatter.format(new Date(2024, index, 1))
    return {
      value: index + 1,
      label: label.charAt(0).toUpperCase() + label.slice(1)
    }
  })
}

export function getYearOptions (centerYear: number, range = 8): number[] {
  const start = centerYear - range
  const end = centerYear + range

  return Array.from({ length: end - start + 1 }, (_, index) => start + index)
}

export function getWeekdayLabels (
  weekStartsOn: CalendarWeekStart = 1,
  locale = 'ru-RU'
): string[] {
  // 2024-01-01 is Monday
  const monday = new Date(2024, 0, 1)
  const startOffset = weekStartsOn === 1 ? 0 : -1
  const formatter = new Intl.DateTimeFormat(locale, { weekday: 'short' })

  return Array.from({ length: 7 }, (_, index) => {
    const day = new Date(monday)
    day.setDate(monday.getDate() + startOffset + index)
    const label = formatter.format(day)

    return label.charAt(0).toUpperCase() + label.slice(1)
  })
}

export function groupEventsByDate (
  events: ICalendarEvent[]
): Map<string, ICalendarEvent[]> {
  const map = new Map<string, ICalendarEvent[]>()

  for (const event of events) {
    if (!DATE_KEY_RE.test(event.date)) continue

    const bucket = map.get(event.date)
    if (bucket) {
      bucket.push(event)
    } else {
      map.set(event.date, [event])
    }
  }

  return map
}

export function buildMonthGrid (options: {
  monthKey: string
  selectedDate: string | null
  events: ICalendarEvent[]
  weekStartsOn?: CalendarWeekStart
  today?: Date
}): ICalendarDayCell[] {
  const weekStartsOn = options.weekStartsOn ?? 1
  const monthStart = parseMonthKey(options.monthKey)
  if (!monthStart) return []

  const todayKey = toDateKey(options.today ?? new Date())
  const eventsByDate = groupEventsByDate(options.events)

  const year = monthStart.getFullYear()
  const month = monthStart.getMonth()
  const firstWeekday = monthStart.getDay()
  const leading = weekStartsOn === 1
    ? (firstWeekday + 6) % 7
    : firstWeekday

  const gridStart = new Date(year, month, 1 - leading)
  const cells: ICalendarDayCell[] = []

  for (let index = 0; index < 42; index += 1) {
    const current = new Date(gridStart)
    current.setDate(gridStart.getDate() + index)
    const date = toDateKey(current)

    cells.push({
      date,
      day: current.getDate(),
      inCurrentMonth: current.getMonth() === month,
      isToday: date === todayKey,
      isSelected: Boolean(options.selectedDate && date === options.selectedDate),
      events: eventsByDate.get(date) ?? []
    })
  }

  return cells
}

export function formatEventTime (datetime: string): string | null {
  const match = datetime.trim().match(/\b(\d{1,2}):(\d{2})\b/)
  if (!match) return null

  return `${String(match[1]).padStart(2, '0')}:${match[2]}`
}
