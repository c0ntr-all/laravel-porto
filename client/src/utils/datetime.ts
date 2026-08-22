import { date } from 'quasar'

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

export { getCurrentDateTime, humanDatetime, formatPresetDateRange }
