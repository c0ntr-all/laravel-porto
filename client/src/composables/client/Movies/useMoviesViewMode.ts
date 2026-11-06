import { MoviesViewModeEnum } from 'src/enums/Movie/MoviesViewModeEnum'
import { usePersistedEnum } from 'src/composables/usePersistedEnum'

const STORAGE_KEY = 'movies.viewMode'

export function useMoviesViewMode() {
  const { value: viewMode } = usePersistedEnum<MoviesViewModeEnum>(
    STORAGE_KEY,
    [MoviesViewModeEnum.LIST, MoviesViewModeEnum.TILE],
    MoviesViewModeEnum.TILE
  )

  return { viewMode }
}
