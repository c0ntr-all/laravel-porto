import addZero from 'src/utils/addZero'

export function parseDuration(value: string | number | null | undefined): number {
  if (typeof value === 'number') {
    return Number.isFinite(value) && value > 0 ? value : 0
  }

  if (!value) {
    return 0
  }

  const parts = value.split(':').map(part => Number(part))
  if (parts.some(part => Number.isNaN(part))) {
    return 0
  }

  if (parts.length === 3) {
    return (parts[0] * 3600) + (parts[1] * 60) + parts[2]
  }

  if (parts.length === 2) {
    return (parts[0] * 60) + parts[1]
  }

  return parts[0] ?? 0
}

export function formatPlaybackTime(seconds: number): string {
  if (!Number.isFinite(seconds) || seconds < 0) {
    return '0:00'
  }

  const total = Math.floor(seconds)
  const hours = Math.floor(total / 3600)
  const minutes = Math.floor((total % 3600) / 60)
  const secs = total % 60

  if (hours > 0) {
    return `${hours}:${addZero(minutes)}:${addZero(secs)}`
  }

  return `${minutes}:${addZero(secs)}`
}
