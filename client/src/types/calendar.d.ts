/**
 * Event rendered inside a calendar day cell (planning / Outlook-style month view).
 */
export interface ICalendarEvent {
  id: string
  /** Local calendar date in YYYY-MM-DD */
  date: string
  title: string
  /** Optional time label, e.g. HH:mm */
  time?: string | null
  color?: string
}

export interface ICalendarDayCell {
  /** YYYY-MM-DD */
  date: string
  day: number
  inCurrentMonth: boolean
  isToday: boolean
  isSelected: boolean
  events: ICalendarEvent[]
}

export type CalendarWeekStart = 0 | 1
