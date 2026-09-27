/**
 * Normalizes movie watch started_at for API: Y-m-d or Y-m-d H:i
 */
export function normalizeStartedAt (value: unknown): string | null {
  if (typeof value !== 'string') {
    return null
  }

  const trimmed = value.trim()
  if (!trimmed) {
    return null
  }

  if (/^\d{4}-\d{2}-\d{2}$/.test(trimmed)) {
    return trimmed
  }

  const match = trimmed.match(/^(\d{4}-\d{2}-\d{2})[ T](\d{1,2}):(\d{2})(?::\d{2})?$/)
  if (!match) {
    return null
  }

  const hour = Number(match[2])
  const minute = Number(match[3])
  if (hour < 0 || hour > 23 || minute < 0 || minute > 59) {
    return null
  }

  return `${match[1]} ${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`
}

export function serializeStartedAt (
  datetime: string | null | undefined,
  isNullTime: boolean
): string | null {
  if (!datetime?.trim()) {
    return null
  }

  const [datePart, timePart] = datetime.trim().split(' ')
  if (!datePart || !/^\d{4}-\d{2}-\d{2}$/.test(datePart)) {
    return null
  }

  if (isNullTime || !timePart) {
    return datePart
  }

  return normalizeStartedAt(`${datePart} ${timePart}`)
}

export function parseStartedAtToForm (value: string | null | undefined): {
  datetime: string
  isNullTime: boolean
} {
  const normalized = normalizeStartedAt(value)
  if (!normalized) {
    return { datetime: '', isNullTime: true }
  }

  if (normalized.includes(' ')) {
    return { datetime: normalized, isNullTime: false }
  }

  return { datetime: normalized, isNullTime: true }
}

export function formatStartedAtDisplay (value: string | null | undefined): string {
  const normalized = normalizeStartedAt(value)
  if (!normalized) {
    return ''
  }

  return normalized
}
