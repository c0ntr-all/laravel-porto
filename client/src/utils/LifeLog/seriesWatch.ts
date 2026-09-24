import { PostContentTypeEnum } from 'src/enums/LifeLog/PostContentTypeEnum'
import { MovieTypeEnum } from 'src/enums/Movie/MovieTypeEnum'
import { IPost } from 'src/types'
import { ISeriesWatchProgress } from 'src/types/LifeLog/watch'

function formatStoppedAtTime (value: string): string {
  if (value.length === 8) {
    return value.slice(0, 5)
  }

  return value
}

export function emptySeriesWatchProgress (): ISeriesWatchProgress {
  return {
    season: 1,
    episode_from: 1,
    episode_to: 1,
    stopped_at: null,
    episode_ids: []
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

  const episodeIds = Array.isArray(raw.episode_ids)
    ? raw.episode_ids.map(id => String(id)).filter(Boolean)
    : undefined

  return {
    season,
    episode_from: episodeFrom,
    episode_to: episodeTo,
    stopped_at: stoppedAt,
    ...(episodeIds?.length ? { episode_ids: episodeIds } : {})
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

export function isSeriesWatchPost (post: IPost): boolean {
  if (post.content_type === PostContentTypeEnum.TV_SERIES) {
    return true
  }

  return post.movie?.type === MovieTypeEnum.TV_SERIES
}

export function formatSeriesWatchProgress (watch: ISeriesWatchProgress | null | undefined): string {
  const normalized = normalizeSeriesWatchProgress(watch)
  if (!normalized) {
    return ''
  }

  const episodes = normalized.episode_from === normalized.episode_to
    ? `эпизод ${normalized.episode_from}`
    : `эпизоды ${normalized.episode_from}–${normalized.episode_to}`

  let text = `Сезон ${normalized.season}, ${episodes}`

  if (normalized.stopped_at) {
    text += ` · останов на ${formatStoppedAtTime(normalized.stopped_at)}`
  }

  return text
}

export function isSeriesWatchValid (
  watch: ISeriesWatchProgress | null | undefined,
  options?: { requireEpisodeSelection?: boolean }
): boolean {
  if (!normalizeSeriesWatchProgress(watch)) {
    return false
  }

  if (options?.requireEpisodeSelection) {
    const ids = watch?.episode_ids
    return Array.isArray(ids) && ids.length > 0
  }

  return true
}
