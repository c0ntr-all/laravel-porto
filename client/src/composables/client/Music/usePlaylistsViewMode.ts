import { ref, watch } from 'vue'
import { PlaylistsViewModeEnum } from 'src/enums/Music/PlaylistsViewModeEnum'

const STORAGE_KEY = 'music.playlists.viewMode'

function readViewMode (): PlaylistsViewModeEnum {
  if (typeof window === 'undefined') {
    return PlaylistsViewModeEnum.BLOCKS
  }

  const stored = localStorage.getItem(STORAGE_KEY)

  if (stored === PlaylistsViewModeEnum.LIST || stored === PlaylistsViewModeEnum.BLOCKS) {
    return stored
  }

  return PlaylistsViewModeEnum.BLOCKS
}

export function usePlaylistsViewMode () {
  const viewMode = ref(readViewMode())

  watch(viewMode, (value) => {
    localStorage.setItem(STORAGE_KEY, value)
  })

  return { viewMode }
}
