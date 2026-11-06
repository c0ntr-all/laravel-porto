import { PlaylistsViewModeEnum } from 'src/enums/Music/PlaylistsViewModeEnum'
import { usePersistedEnum } from 'src/composables/usePersistedEnum'

const STORAGE_KEY = 'music.playlists.viewMode'

export function usePlaylistsViewMode() {
  const { value: viewMode } = usePersistedEnum<PlaylistsViewModeEnum>(
    STORAGE_KEY,
    [PlaylistsViewModeEnum.LIST, PlaylistsViewModeEnum.BLOCKS],
    PlaylistsViewModeEnum.BLOCKS
  )

  return { viewMode }
}
