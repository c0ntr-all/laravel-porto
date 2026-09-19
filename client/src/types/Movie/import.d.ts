import { MovieImportStatusEnum } from 'src/enums/Movie/MovieImportStatusEnum'
import { IMovie } from './movie'

export interface IMovieImport {
  id: string
  kp_id: number
  movie_id: number | null
  source_url: string
  status: MovieImportStatusEnum
  http_status: number | null
  was_created: boolean | null
  parsed_payload: Record<string, unknown> | null
  meta: Record<string, unknown> | null
  error_message: string | null
  started_at: string | null
  finished_at: string | null
  duration_ms: number | null
  created_at: string | null
  movie: IMovie | null
}

export interface IMovieImportListQuery {
  kp_id?: number
  status?: MovieImportStatusEnum
  cursor?: string | null
}
