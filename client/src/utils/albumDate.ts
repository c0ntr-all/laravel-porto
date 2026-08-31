export function albumYear (date?: string | null): string {
  if (!date) {
    return ''
  }

  const year = Number.parseInt(date.slice(0, 4), 10)
  if (Number.isFinite(year) && year > 0) {
    return String(year)
  }

  const parsed = new Date(date).getFullYear()
  return Number.isFinite(parsed) ? String(parsed) : ''
}
