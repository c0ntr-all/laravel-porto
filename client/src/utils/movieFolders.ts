import { IMovie, IMovieFolder } from 'src/types/Movie'
import { SystemMovieFolderEnum } from 'src/enums/Movie/SystemMovieFolderEnum'

export const MOVIE_FOLDER_NAME_MAX = 30
export const MOVIE_FOLDER_NAME_PATTERN = /^[\p{L}\p{N}]+$/u

const SELECT_ORDER: SystemMovieFolderEnum[] = [
  SystemMovieFolderEnum.WATCHLIST,
  SystemMovieFolderEnum.FAVORITES,
  SystemMovieFolderEnum.WATCHED
]

export function sanitizeMovieFolderName(value: string): string {
  return value.replace(/[^\p{L}\p{N}]/gu, '').slice(0, MOVIE_FOLDER_NAME_MAX)
}

export function isValidMovieFolderName(value: string): boolean {
  return MOVIE_FOLDER_NAME_PATTERN.test(value) && value.length <= MOVIE_FOLDER_NAME_MAX
}

export function sortedMovieFolders(folders: IMovieFolder[]): IMovieFolder[] {
  const system = SELECT_ORDER
    .map(slug => folders.find(folder => folder.slug === slug))
    .filter((folder): folder is IMovieFolder => Boolean(folder))

  const rest = folders
    .filter(folder => (
      folder.slug !== SystemMovieFolderEnum.WATCHLIST &&
      folder.slug !== SystemMovieFolderEnum.FAVORITES &&
      folder.slug !== SystemMovieFolderEnum.WATCHED
    ))
    .slice()
    .sort((left, right) => left.name.localeCompare(right.name, 'ru', { sensitivity: 'base' }))

  return [...system, ...rest]
}

export function isMovieInFolderRecord(movie: IMovie, folder: IMovieFolder): boolean {
  if ((movie.folder_ids ?? []).map(String).includes(String(folder.id))) {
    return true
  }

  return Boolean(folder.slug && (movie.folder_slugs ?? []).includes(folder.slug))
}

export function withFolderMembership(
  movie: IMovie,
  folder: IMovieFolder,
  present: boolean
): IMovie {
  const slugs = new Set(movie.folder_slugs ?? [])
  const ids = new Set((movie.folder_ids ?? []).map(String))

  if (present) {
    if (folder.slug) {
      slugs.add(folder.slug)
    }

    ids.add(String(folder.id))
  } else {
    if (folder.slug) {
      slugs.delete(folder.slug)
    }

    ids.delete(String(folder.id))
  }

  return {
    ...movie,
    folder_slugs: [...slugs],
    folder_ids: [...ids]
  }
}
