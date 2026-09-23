import { ISeriesWatchProgress } from 'src/types/LifeLog/watch'

export function emptySeriesWatchProgress (): ISeriesWatchProgress {
  return {
    season: 1,
    episode_from: 1,
    episode_to: 1,
    stopped_at: null
  }
}

export function normalizeSeriesWatchProgress (value: unknown): ISeriesWatchProgress | null {
  if (!value || typeof value !== 'object') {
    return null
  }

  const raw = value as Record<string, unknown>
  const season = Number(raw.season)
  const episodeFrom = Number(raw.episode_from)
  const episodeTo = Number(raw.episode_to)

  if (
    !Number.isFinite(season) ||
    season < 1 ||
    !Number.isFinite(episodeFrom) ||
    episodeFrom < 1 ||
    !Number.isFinite(episodeTo) ||
    episodeTo < episodeFrom
  ) {
    return null
  }

  const stoppedAt = typeof raw.stopped_at === 'string' && raw.stopped_at.trim()
    ? raw.stopped_at.trim()
    : null

  return {
    season,
    episode_from: episodeFrom,
    episode_to: episodeTo,
    stopped_at: stoppedAt
  }
}

export function serializeSeriesWatchProgress (
  watch: ISeriesWatchProgress | null | undefined
): ISeriesWatchProgress | undefined {
  const normalized = normalizeSeriesWatchProgress(watch)
  if (!normalized) {
    return undefined
  }

  const payload: ISeriesWatchProgress = {
    season: normalized.season,
    episode_from: normalized.episode_from,
    episode_to: normalized.episode_to
  }

  if (normalized.stopped_at) {
    payload.stopped_at = normalized.stopped_at
  }

  return payload
}

export function formatSeriesWatchProgress (watch: ISeriesWatchProgress | null | undefined): string {
  const normalized = normalizeSeriesWatchProgress(watch)
  if (!normalized) {
    return ''
  }

  const range = normalized.episode_from === normalized.episode_to
    ? `S${normalized.season}E${normalized.episode_from}`
    : `S${normalized.season}E${normalized.episode_from}–E${normalized.episode_to}`

  if (normalized.stopped_at) {
    const time = normalized.stopped_at.length === 8
      ? normalized.stopped_at.slice(0, 5)
      : normalized.stopped_at

    return `${range} · стоп ${time}`
  }

  return range
}

export function isSeriesWatchValid (watch: ISeriesWatchProgress | null | undefined): boolean {
  return normalizeSeriesWatchProgress(watch) !== null
}
