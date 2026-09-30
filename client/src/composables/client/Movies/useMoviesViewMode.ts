import { ref, watch } from 'vue'
import { MoviesViewModeEnum } from 'src/enums/Movie/MoviesViewModeEnum'

const STORAGE_KEY = 'movies.viewMode'

function readViewMode(): MoviesViewModeEnum {
  if (typeof window === 'undefined') {
    return MoviesViewModeEnum.TILE
  }

  const stored = localStorage.getItem(STORAGE_KEY)

  if (stored === MoviesViewModeEnum.LIST || stored === MoviesViewModeEnum.TILE) {
    return stored
  }

  return MoviesViewModeEnum.TILE
}

export function useMoviesViewMode() {
  const viewMode = ref(readViewMode())

  watch(viewMode, (value) => {
    localStorage.setItem(STORAGE_KEY, value)
  })

  return { viewMode }
}
