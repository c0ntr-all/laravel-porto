import { MovieTypeEnum } from 'src/enums/Movie/MovieTypeEnum'

export interface IMovieGenre {
  id: string
  kp_id: number | null
  name: string
  slug: string
}

export interface IMovieCountry {
  id: string
  name: string
  description: string | null
  image: string | null
}

export interface IMovie {
  id: string
  kp_id: number
  title: string
  year: number
  type: MovieTypeEnum
  cover: string | null
  kp_rating: number | null
  kp_img: string | null
  created_at: string | null
  updated_at: string | null
  genres: IMovieGenre[]
  countries: IMovieCountry[]
}

export interface IMovieListQuery {
  title?: string
  year?: number
  type?: MovieTypeEnum
  genre_id?: Array<string | number>
  country_id?: Array<string | number>
  sort?: string
  cursor?: string | null
}
