function getCurrentDateTime() {
  const now = new Date();

  const datePart = new Intl.DateTimeFormat('sv-SE').format(now)

  const timePart = new Intl.DateTimeFormat('ru-RU', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  }).format(now)

  return `${datePart} ${timePart}`
}

export default getCurrentDateTime
