import { IArtistShort } from './artist'
import { IMusicTag } from './tag'

export interface ITrack {
  id: string
  name: string
  image: string
  duration: string
  rate: number
  artist: string
  number?: number
  artists?: IArtistShort[]
  tags?: IMusicTag[]
}

export interface ITrackListQuery {
  name?: string
  cursor?: string | null
}
