const KP_ID_IN_PATH = /\/(?:film|series)\/(\d+)/i

export function parseKinopoiskId(value: string | number | null | undefined): number | null {
  if (typeof value === 'number') {
    return Number.isInteger(value) && value >= 1 ? value : null
  }

  const trimmed = String(value ?? '').trim()

  if (!trimmed) {
    return null
  }

  if (/^\d+$/.test(trimmed)) {
    const kpId = Number(trimmed)

    return kpId >= 1 ? kpId : null
  }

  try {
    const url = new URL(trimmed)
    const fromPath = url.pathname.match(KP_ID_IN_PATH)

    if (fromPath?.[1]) {
      return Number(fromPath[1])
    }
  } catch {
    // not a full URL; fall through to a loose path match
  }

  const loose = trimmed.match(KP_ID_IN_PATH)

  return loose?.[1] ? Number(loose[1]) : null
}
