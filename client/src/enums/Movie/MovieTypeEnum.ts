export enum MovieTypeEnum {
  MOVIE = 'movie',
  TV_SERIES = 'tv_series',
  SHOW = 'show'
}

export const MOVIE_TYPES = Object.values(MovieTypeEnum)

export const MOVIE_TYPE_LABELS: Record<MovieTypeEnum, string> = {
  [MovieTypeEnum.MOVIE]: 'Фильм',
  [MovieTypeEnum.TV_SERIES]: 'Сериал',
  [MovieTypeEnum.SHOW]: 'Шоу'
}
