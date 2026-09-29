import { IMovie } from './movie'

export interface IMovieFranchise {
  id: string
  user_id: number
  name: string
  description: string | null
  image: string | null
  order: number
  created_at: string | null
  updated_at: string | null
}

export interface IMovieFranchiseWriteDto {
  name?: string
  description?: string | null
  order?: number
  image_file?: File | null
}

export interface IMovieFranchiseMovie extends IMovie {
  order: number
}
