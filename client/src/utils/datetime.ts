import { date } from 'quasar'

function pad2 (value: string | number): string {
  return String(value).padStart(2, '0')
}

function isValidCalendarDate (year: number, month: number, day: number): boolean {
  if (month < 1 || month > 12 || day < 1 || day > 31) {
    return false
  }

  const probe = new Date(year, month - 1, day)

  return (
    probe.getFullYear() === year &&
    probe.getMonth() === month - 1 &&
    probe.getDate() === day
  )
}

/**
 * Приводит вставленную или введённую дату/время к формату модели: YYYY-MM-DD HH:mm
 */
function normalizeDatetimeInput (value: string): string | null {
  const trimmed = value.trim()
  if (!trimmed) {
    return null
  }

  const isoMatch = trimmed.match(
    /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{1,2}):(\d{2})(?::(\d{2}))?)?$/
  )
  if (isoMatch) {
    const year = Number(isoMatch[1])
    const month = Number(isoMatch[2])
    const day = Number(isoMatch[3])
    if (!isValidCalendarDate(year, month, day)) {
      return null
    }

    const hour = isoMatch[4] !== undefined ? Number(isoMatch[4]) : 0
    const minute = isoMatch[5] !== undefined ? Number(isoMatch[5]) : 0
    if (hour < 0 || hour > 23 || minute < 0 || minute > 59) {
      return null
    }

    return `${isoMatch[1]}-${isoMatch[2]}-${isoMatch[3]} ${pad2(hour)}:${pad2(minute)}`
  }

  const dmyTimeMatch = trimmed.match(
    /^(\d{1,2})\.(\d{1,2})\.(\d{4})[\s,]*(\d{1,2}):(\d{2})(?::(\d{2}))?$/
  )
  if (dmyTimeMatch) {
    const day = Number(dmyTimeMatch[1])
    const month = Number(dmyTimeMatch[2])
    const year = Number(dmyTimeMatch[3])
    const hour = Number(dmyTimeMatch[4])
    const minute = Number(dmyTimeMatch[5])

    if (
      !isValidCalendarDate(year, month, day) ||
      hour < 0 ||
      hour > 23 ||
      minute < 0 ||
      minute > 59
    ) {
      return null
    }

    return `${year}-${pad2(month)}-${pad2(day)} ${pad2(hour)}:${pad2(minute)}`
  }

  const dmyOnlyMatch = trimmed.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/)
  if (dmyOnlyMatch) {
    const day = Number(dmyOnlyMatch[1])
    const month = Number(dmyOnlyMatch[2])
    const year = Number(dmyOnlyMatch[3])

    if (!isValidCalendarDate(year, month, day)) {
      return null
    }

    return `${year}-${pad2(month)}-${pad2(day)} 00:00`
  }

  return null
}

/**
 * Приводит вставленную или введённую дату к формату модели: YYYY-MM-DD
 */
function normalizeDateInput (value: string): string | null {
  const trimmed = value.trim()
  if (!trimmed) {
    return null
  }

  const isoMatch = trimmed.match(/^(\d{4})-(\d{2})-(\d{2})$/)
  if (isoMatch) {
    const year = Number(isoMatch[1])
    const month = Number(isoMatch[2])
    const day = Number(isoMatch[3])
    if (!isValidCalendarDate(year, month, day)) {
      return null
    }

    return `${isoMatch[1]}-${isoMatch[2]}-${isoMatch[3]}`
  }

  const dmyMatch = trimmed.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/)
  if (dmyMatch) {
    const day = Number(dmyMatch[1])
    const month = Number(dmyMatch[2])
    const year = Number(dmyMatch[3])

    if (!isValidCalendarDate(year, month, day)) {
      return null
    }

    return `${year}-${pad2(month)}-${pad2(day)}`
  }

  const fromDatetime = normalizeDatetimeInput(trimmed)
  if (fromDatetime) {
    return fromDatetime.split(' ')[0]
  }

  return null
}

function getCurrentDateTime() {
  const now = new Date()

  const datePart = new Intl.DateTimeFormat('sv-SE').format(now)

  const timePart = new Intl.DateTimeFormat('ru-RU', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  }).format(now)

  return `${datePart} ${timePart}`
}

const humanDatetime = (datetime: string) => {
  const year = date.formatDate(datetime, 'YYYY')
  const currentYear = date.formatDate(new Date(), 'YYYY')
  let format = 'D MMM, HH:mm'
  if (year < currentYear || year > currentYear) {
    format = 'D MMM YYYY, HH:mm'
  }
  return date.formatDate(datetime, format)
}

const formatPresetDateRange = (
  dateFrom: string | null,
  dateTo: string | null
): string => {
  if (!dateFrom && !dateTo) {
    return 'Даты не заданы'
  }

  if (dateFrom && dateTo) {
    return `${humanDatetime(dateFrom)} — ${humanDatetime(dateTo)}`
  }

  if (dateFrom) {
    return `с ${humanDatetime(dateFrom)}`
  }

  return `до ${humanDatetime(dateTo!)}`
}

export {
  getCurrentDateTime,
  humanDatetime,
  formatPresetDateRange,
  normalizeDatetimeInput,
  normalizeDateInput
}
