export interface IMovieProfession {
  id: string
  en_name: string
  name: string | null
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
}

export interface IMovieCredit {
  id: string
  description: string | null
  person: Pick<IMoviePerson, 'id' | 'name' | 'en_name' | 'photo'>
  profession: IMovieProfession
}

export interface IMovieCreditGroup {
  profession: IMovieProfession
  credits: IMovieCredit[]
}
