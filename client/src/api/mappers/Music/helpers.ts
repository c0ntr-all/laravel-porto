export function asRecords(value: unknown): Record<string, unknown>[] {
  if (!value) {
    return []
  }

  if (Array.isArray(value)) {
    return value.filter((item): item is Record<string, unknown> => (
      Boolean(item) && typeof item === 'object'
    ))
  }

  if (typeof value === 'object') {
    return [value as Record<string, unknown>]
  }

  return []
}

export function asRecord(value: unknown): Record<string, unknown> | null {
  if (!value || typeof value !== 'object' || Array.isArray(value)) {
    return null
  }

  return value as Record<string, unknown>
}
