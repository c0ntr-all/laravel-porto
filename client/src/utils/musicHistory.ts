import { date } from 'quasar'
import { IHistoryItem } from 'src/types'

export interface IHistoryDayGroup {
  day: string
  label: string
  items: IHistoryItem[]
}

function historyDayKey (createdAt: string | null): string {
  if (!createdAt) {
    return ''
  }

  return createdAt.trim().slice(0, 10)
}

export function formatHistoryDayLabel (dayKey: string): string {
  if (!dayKey) {
    return 'Unknown date'
  }

  const parsed = date.extractDate(dayKey, 'YYYY-MM-DD')
  if (!parsed || Number.isNaN(parsed.getTime())) {
    return dayKey
  }

  const today = date.formatDate(new Date(), 'YYYY-MM-DD')
  const yesterdayDate = new Date()
  yesterdayDate.setDate(yesterdayDate.getDate() - 1)
  const yesterday = date.formatDate(yesterdayDate, 'YYYY-MM-DD')

  if (dayKey === today) {
    return 'Today'
  }

  if (dayKey === yesterday) {
    return 'Yesterday'
  }

  const currentYear = date.formatDate(new Date(), 'YYYY')
  const year = date.formatDate(parsed, 'YYYY')
  const format = year === currentYear ? 'D MMMM' : 'D MMMM YYYY'

  return date.formatDate(parsed, format)
}

export function formatHistoryTime (createdAt: string | null): string {
  if (!createdAt) {
    return ''
  }

  const trimmed = createdAt.trim()
  const parsed = date.extractDate(trimmed, 'YYYY-MM-DD HH:mm:ss') ||
    date.extractDate(trimmed, 'YYYY-MM-DD HH:mm') ||
    date.extractDate(trimmed, 'YYYY-MM-DDTHH:mm:ss')

  if (!parsed || Number.isNaN(parsed.getTime())) {
    const match = trimmed.match(/(\d{1,2}):(\d{2})/)
    return match ? `${match[1].padStart(2, '0')}:${match[2]}` : ''
  }

  return date.formatDate(parsed, 'HH:mm')
}

export function groupHistoryByDay (items: IHistoryItem[]): IHistoryDayGroup[] {
  const groups: IHistoryDayGroup[] = []
  const indexByDay = new Map<string, number>()

  for (const item of items) {
    const day = historyDayKey(item.created_at)
    const existing = indexByDay.get(day)

    if (existing === undefined) {
      indexByDay.set(day, groups.length)
      groups.push({
        day,
        label: formatHistoryDayLabel(day),
        items: [item]
      })
      continue
    }

    groups[existing]?.items.push(item)
  }

  return groups
}
