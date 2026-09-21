export interface IMovieFolder {
  id: string
  user_id: number
  name: string
  slug: string | null
  is_system: boolean
  movies_count: number
  created_at: string | null
  updated_at: string | null
}
