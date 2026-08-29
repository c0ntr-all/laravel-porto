import { IArtistShort } from './artist'

export interface IAlbum {
  id: string
  name: string
  date: string | null
  image: string
  artists: IArtistShort[]
}
