import { AxiosError } from 'axios'
import { Notify } from 'quasar'
import { isRequestCanceled } from 'src/utils/apiNotify'
import { pluralizeRu } from 'src/utils/movieLabels'

const STEAM_APP_ID = /\/app\/(\d+)/i

export function steamStoreUrl(steamId: number): string | null {
  if (!Number.isInteger(steamId) || steamId < 1) {
    return null
  }

  return `https://store.steampowered.com/app/${steamId}`
}

export function parseSteamId(value: string | number | null | undefined): number | null {
  if (typeof value === 'number') {
    return Number.isInteger(value) && value >= 1 ? value : null
  }

  const trimmed = String(value ?? '').trim()

  if (!trimmed) {
    return null
  }

  if (/^\d+$/.test(trimmed)) {
    const steamId = Number(trimmed)

    return steamId >= 1 ? steamId : null
  }

  try {
    const url = new URL(trimmed)
    const fromPath = url.pathname.match(STEAM_APP_ID)

    if (fromPath?.[1]) {
      return Number(fromPath[1])
    }
  } catch {
    // not a full URL; fall through to a loose path match
  }

  const loose = trimmed.match(STEAM_APP_ID)

  return loose?.[1] ? Number(loose[1]) : null
}

export function formatGameAirDate(value: string | null | undefined): string {
  const day = String(value ?? '').trim().slice(0, 10)

  if (!day) {
    return ''
  }

  const parsed = new Date(`${day}T00:00:00`)

  if (Number.isNaN(parsed.getTime())) {
    return day
  }

  return new Intl.DateTimeFormat('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(parsed)
}

export function formatGamesCount(count: number): string {
  return `${count} ${pluralizeRu(count, ['игра', 'игры', 'игр'])}`
}

export function notifyGameApiError(error: unknown): void {
  if (isRequestCanceled(error)) {
    return
  }

  let message = 'Не удалось выполнить запрос'

  if (error instanceof AxiosError) {
    const data = error.response?.data as { message?: string } | undefined
    message = data?.message || error.message
  } else if (error instanceof Error) {
    message = error.message
  }

  Notify.create({
    type: 'negative',
    message
  })
}
