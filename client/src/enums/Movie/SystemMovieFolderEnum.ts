export enum SystemMovieFolderEnum {
  WATCHLIST = 'watchlist',
  WATCHED = 'watched',
  FAVORITES = 'favorites'
}

export const SYSTEM_MOVIE_FOLDER_LABELS: Record<SystemMovieFolderEnum, string> = {
  [SystemMovieFolderEnum.WATCHLIST]: 'Буду смотреть',
  [SystemMovieFolderEnum.WATCHED]: 'Просмотрено',
  [SystemMovieFolderEnum.FAVORITES]: 'Любимые фильмы'
}
