import { ITrack } from './track'

export interface IHistoryItem {
  id: string
  track_id: string
  /** Listened share of the track at scrobble time, percent 0..100 rounded to hundredths. */
  progress?: number | null
  created_at: string | null
  track: ITrack | null
}

export interface IHistoryListQuery {
  cursor?: string | null
}
