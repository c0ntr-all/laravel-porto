import { ITrack } from './track'

export interface IPlaylist {
  id: string
  name: string
  description: string | null
  image: string
  created_at?: string
  tracks: ITrack[]
}
