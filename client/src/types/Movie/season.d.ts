export interface IMovieEpisode {
  id: string
  season_id: string
  number: number
  name: string | null
  description: string | null
  air_date: string | null
  still: string | null
  still_preview: string | null
  is_watched: boolean
  watched_at: string | null
}

export interface IMovieSeason {
  id: string
  movie_id: string
  number: number
  name: string | null
  air_date: string | null
  episodes_count: number | null
  poster: string | null
  poster_preview: string | null
  is_watched: boolean
  watched_at: string | null
  episodes: IMovieEpisode[]
}
