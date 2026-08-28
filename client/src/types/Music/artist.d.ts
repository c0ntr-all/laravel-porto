import { IMusicTag } from './tag'

export interface IArtistShort {
  id: string
  name: string
}

export interface IArtist {
  id: string
  name: string
  description: string | null
  image: string
  created_at?: string
  tags: IMusicTag[]
}
