import { ITrack } from './track'

export interface IHistoryItem {
  id: string
  track_id: string
  created_at: string | null
  track: ITrack | null
}

export interface IHistoryListQuery {
  cursor?: string | null
}
