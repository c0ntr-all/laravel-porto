export interface ISeriesWatchProgress {
  season: number
  episode_from: number
  episode_to: number
  stopped_at?: string | null
  /** Выбранные эпизоды в форме; в API не отправляется */
  episode_ids?: string[]
}
