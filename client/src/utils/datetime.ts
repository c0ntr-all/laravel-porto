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

const humanDatetime = datetime => {
  const year = date.formatDate(datetime, 'YYYY')
  const currentYear = date.formatDate(new Date(), 'YYYY')
  let format = 'D MMM, HH:mm'
  if (year < currentYear || year > currentYear) {
    format = 'D MMM YYYY, HH:mm'
  }
  return date.formatDate(datetime, format)
}

export { getCurrentDateTime, humanDatetime }
