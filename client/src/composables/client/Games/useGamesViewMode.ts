import { GamesViewModeEnum } from 'src/enums/Game/GamesViewModeEnum'
import { usePersistedEnum } from 'src/composables/usePersistedEnum'

const STORAGE_KEY = 'games.viewMode'

export function useGamesViewMode() {
  const { value: viewMode } = usePersistedEnum<GamesViewModeEnum>(
    STORAGE_KEY,
    [GamesViewModeEnum.LIST, GamesViewModeEnum.TILE],
    GamesViewModeEnum.TILE
  )

  return { viewMode }
}
