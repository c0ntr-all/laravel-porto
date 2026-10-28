import { ref, watch } from 'vue'
import { GamesViewModeEnum } from 'src/enums/Game/GamesViewModeEnum'

const STORAGE_KEY = 'games.viewMode'

function readViewMode(): GamesViewModeEnum {
  if (typeof window === 'undefined') {
    return GamesViewModeEnum.TILE
  }

  const stored = localStorage.getItem(STORAGE_KEY)

  if (stored === GamesViewModeEnum.LIST || stored === GamesViewModeEnum.TILE) {
    return stored
  }

  return GamesViewModeEnum.TILE
}

export function useGamesViewMode() {
  const viewMode = ref(readViewMode())

  watch(viewMode, (value) => {
    localStorage.setItem(STORAGE_KEY, value)
  })

  return { viewMode }
}
