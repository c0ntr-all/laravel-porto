import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import axios from 'axios'
import { playlistApi } from 'src/api/requests/playlistApi'
import { trackApi } from 'src/api/requests/trackApi'
import { mapPlaylistResponse, mapPlaylistsResponse } from 'src/api/mappers/Music/playlist.mapper'
import { mapTracksResponse } from 'src/api/mappers/Music/track.mapper'
import { extractCursorFromLink, handleApiError, handleApiSuccess } from 'src/utils/jsonapi'
import { IPlaylist, ITrack } from 'src/types'

export const TRACK_SEARCH_MIN_LENGTH = 3
const SEARCH_DEBOUNCE_MS = 400

export const useMusicPlaylistStore = defineStore('musicPlaylist', () => {
  const playlist = ref<IPlaylist | null>(null)
  const isPlaylistLoading = ref(false)

  const searchQuery = ref('')
  const searchResults = ref<ITrack[]>([])
  const searchCursor = ref<string | null>(null)
  const isSearching = ref(false)
  const isSearchingMore = ref(false)
  const addingTrackIds = ref<string[]>([])

  const playlistTrackIds = computed(() => new Set(
    (playlist.value?.tracks ?? []).map(track => track.id)
  ))

  let searchTimer: ReturnType<typeof setTimeout> | undefined
  let searchAbort: AbortController | null = null
  let searchRequestId = 0

  function isInPlaylist(trackId: string): boolean {
    return playlistTrackIds.value.has(trackId)
  }

  function isAddingTrack(trackId: string): boolean {
    return addingTrackIds.value.includes(trackId)
  }

  function resetSearch(): void {
    if (searchTimer) {
      clearTimeout(searchTimer)
      searchTimer = undefined
    }
    searchAbort?.abort()
    searchAbort = null
    searchRequestId += 1
    searchQuery.value = ''
    searchResults.value = []
    searchCursor.value = null
    isSearching.value = false
    isSearchingMore.value = false
  }

  function appendTrack(track: ITrack): void {
    if (!playlist.value || isInPlaylist(track.id)) {
      return
    }

    playlist.value = {
      ...playlist.value,
      tracks: [...playlist.value.tracks, track]
    }
  }

  function removeTrackLocal(trackId: string): void {
    if (!playlist.value) {
      return
    }

    playlist.value = {
      ...playlist.value,
      tracks: playlist.value.tracks.filter(track => track.id !== trackId)
    }
  }

  async function getPlaylist(id: string): Promise<IPlaylist | null> {
    if (playlist.value?.id !== id) {
      playlist.value = null
      resetSearch()
    }

    isPlaylistLoading.value = true

    try {
      const response = await playlistApi.getPlaylist(id)
      playlist.value = mapPlaylistResponse(response)

      return playlist.value
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isPlaylistLoading.value = false
    }
  }

  async function searchTracks(query: string, options?: { append?: boolean }): Promise<void> {
    const append = Boolean(options?.append)
    const term = query.trim()

    if (term.length < TRACK_SEARCH_MIN_LENGTH) {
      searchResults.value = []
      searchCursor.value = null
      isSearching.value = false
      isSearchingMore.value = false
      return
    }

    if (append) {
      if (!searchCursor.value || isSearchingMore.value) {
        return
      }
      isSearchingMore.value = true
    } else {
      searchAbort?.abort()
      searchAbort = new AbortController()
      isSearching.value = true
    }

    const requestId = searchRequestId

    try {
      const response = await trackApi.searchTracks(
        term,
        append ? searchCursor.value : null,
        append ? undefined : searchAbort?.signal
      )

      if (requestId !== searchRequestId) {
        return
      }

      const tracks = mapTracksResponse(response)
      searchResults.value = append ? [...searchResults.value, ...tracks] : tracks
      searchCursor.value = extractCursorFromLink(response.links?.next)
    } catch (error) {
      if (axios.isCancel(error) || (axios.isAxiosError(error) && error.code === 'ERR_CANCELED')) {
        return
      }
      handleApiError(error)
    } finally {
      if (requestId === searchRequestId) {
        isSearching.value = false
        isSearchingMore.value = false
      }
    }
  }

  function setSearchQuery(query: string): void {
    searchQuery.value = query

    if (searchTimer) {
      clearTimeout(searchTimer)
    }

    const term = query.trim()
    if (term.length < TRACK_SEARCH_MIN_LENGTH) {
      searchAbort?.abort()
      searchRequestId += 1
      searchResults.value = []
      searchCursor.value = null
      isSearching.value = false
      isSearchingMore.value = false
      return
    }

    searchAbort?.abort()
    searchRequestId += 1
    isSearching.value = true
    searchTimer = setTimeout(() => {
      void searchTracks(term)
    }, SEARCH_DEBOUNCE_MS)
  }

  async function loadMoreSearchResults(): Promise<void> {
    return searchTracks(searchQuery.value, { append: true })
  }

  async function getPlaylistIdsContainingTrack(trackId: string): Promise<string[]> {
    const ids: string[] = []
    let cursor: string | null = null

    do {
      const response = await playlistApi.listPlaylists(cursor)
      const page = mapPlaylistsResponse(response)

      for (const item of page) {
        if (item.tracks.some(itemTrack => itemTrack.id === trackId)) {
          ids.push(item.id)
        }
      }

      cursor = extractCursorFromLink(response.links?.next)
    } while (cursor)

    return ids
  }

  async function addTrackToPlaylist(track: ITrack): Promise<void> {
    if (!playlist.value || isInPlaylist(track.id) || isAddingTrack(track.id)) {
      return
    }

    addingTrackIds.value = [...addingTrackIds.value, track.id]

    try {
      const playlistIds = [
        ...new Set([
          ...(await getPlaylistIdsContainingTrack(track.id)),
          playlist.value.id
        ])
      ]

      const syncResponse = await trackApi.syncPlaylists(track.id, playlistIds)
      appendTrack(track)
      handleApiSuccess(syncResponse)
    } catch (error) {
      handleApiError(error)
    } finally {
      addingTrackIds.value = addingTrackIds.value.filter(id => id !== track.id)
    }
  }

  return {
    playlist,
    isPlaylistLoading,
    searchQuery,
    searchResults,
    searchCursor,
    isSearching,
    isSearchingMore,
    addingTrackIds,
    playlistTrackIds,
    isInPlaylist,
    isAddingTrack,
    getPlaylist,
    setSearchQuery,
    searchTracks,
    loadMoreSearchResults,
    addTrackToPlaylist,
    removeTrackLocal,
    resetSearch
  }
})
