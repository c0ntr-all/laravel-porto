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

export interface IArtistListQuery {
  name?: string
  cursor?: string | null
  tags?: string[]
  tags_match?: 'and' | 'or'
  tags_nested?: boolean
}
