export interface IMovieProfession {
  id: string
  en_name: string
  name: string | null
}

export interface IPersonCreditMovie {
  id: string
  title: string
  year: number
  type: string
  cover: string | null
  kp_rating: number | null
}

export interface IMoviePerson {
  id: string
  kp_id: number
  name: string
  en_name: string | null
  photo: string | null
  profession: IMovieProfession | null
  professions: IMovieProfession[]
  movies: import('./movie').IMovie[]
  credits: IPersonMovieCredit[]
}

export interface IMovieCredit {
  id: string
  description: string | null
  person: Pick<IMoviePerson, 'id' | 'name' | 'en_name' | 'photo'>
  profession: IMovieProfession
}

export interface IPersonMovieCredit {
  id: string
  description: string | null
  movie: IPersonCreditMovie
  profession: IMovieProfession
}

export interface IMovieCreditGroup {
  profession: IMovieProfession
  credits: IMovieCredit[]
}

export interface IPersonCreditGroup {
  profession: IMovieProfession
  credits: IPersonMovieCredit[]
}
