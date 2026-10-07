export const MOVIE_ACTOR_PROFESSION = 'actor'
export const MOVIE_DIRECTOR_PROFESSION = 'director'
export const MOVIE_PRODUCER_PROFESSION = 'producer'
export const MOVIE_WRITER_PROFESSION = 'writer'
export const MOVIE_SCREENWRITER_PROFESSION = 'screenwriter'

export const MOVIE_ACTORS_SIDEBAR_LIMIT = 10

/** Preferred profession group order on person / filmography pages. */
export const MOVIE_PROFESSION_GROUP_ORDER = [
  MOVIE_ACTOR_PROFESSION,
  MOVIE_DIRECTOR_PROFESSION,
  MOVIE_PRODUCER_PROFESSION,
  MOVIE_WRITER_PROFESSION,
  MOVIE_SCREENWRITER_PROFESSION
] as const
