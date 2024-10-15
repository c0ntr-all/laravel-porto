export interface IRelationshipItem {
  type: string
  id: string
  meta?: Record<string, any>
}
export interface IIncludedItem {
  type: string
  id: string
  attributes: Record<string, any>
  relationships?: any
}
export interface ITagShort {
  id: string
  name: string
  is_base: boolean
}
export interface ITag {
  id: string
  name: string
  content: string | null
  is_base: boolean
  parent_id: string | null
  tags: ITag[]
}

export interface IAlbum {
  id: string
  image: string
  year: number
  name: string
  artist_name: string
  relationships: {
    tags: {
      data: ITagShort[]
    }
  }
}
export interface IResponseAlbum {
  type: string
  id: string
  attributes: {
    name: string
    image: string
    year: number
    artist_name: string
    relationships: {
      tags: {
        data: ITagShort[]
      }
    }
  }
  relationships: any
}
export interface IGetAlbumsResponse {
  data: IResponseAlbum[]
  included: any
}
export interface IArtist {
  id: string
  name: string
  image: string
  description: string | null
  created_at: string
  relationships: {
    tags: {
      data: ITagShort[]
    }
  }
}
export interface IResponseArtist {
  id: string
  attributes: {
    name: string
    image: string
    description: string | null
  }
  relationships: {
    tags: {
      data: IRelationshipItem
    }
  }
}
export interface ITrack {
  id: string
  name: string
  number: number
  artist: string
  image: string
  duration: string
  rate: number
  relationships: {
    tags: {
      data: ITagShort[]
    }
  }
}
