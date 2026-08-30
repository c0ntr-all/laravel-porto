import { defineStore } from 'pinia'
import { ref } from 'vue'
import { artistApi } from 'src/api/requests/artistApi'
import { mapArtistResponse, mapArtistsResponse } from 'src/api/mappers/Music/artist.mapper'
import { mapTracksResponse } from 'src/api/mappers/Music/track.mapper'
import {
  extractCursorFromLink,
  extractCursorFromResponse,
  handleApiError,
  hasMoreFromResponse
} from 'src/utils/jsonapi'
import { IArtist, ITrack } from 'src/types'

function mergeById(current: IArtist[], incoming: IArtist[]): IArtist[] {
  const seen = new Set(current.map(item => item.id))

  return [...current, ...incoming.filter(item => !seen.has(item.id))]
}

export const useMusicCatalogStore = defineStore('musicCatalog', () => {
  const artist = ref<IArtist | null>(null)
  const artistTracks = ref<ITrack[]>([])
  const artistTracksCursor = ref<string | null>(null)
  const isArtistLoading = ref(false)
  const isArtistTracksLoading = ref(false)
  const isArtistTracksLoadingMore = ref(false)

  const artists = ref<IArtist[]>([])
  const artistsCursor = ref<string | null>(null)
  const hasMoreArtists = ref(false)
  const isArtistsLoading = ref(false)
  const isArtistsLoadingMore = ref(false)
  const artistListName = ref('')

  let artistListRequestId = 0

  async function getArtist(id: string): Promise<IArtist | null> {
    if (artist.value?.id !== id) {
      artist.value = null
      artistTracks.value = []
      artistTracksCursor.value = null
    }

    isArtistLoading.value = true

    try {
      const response = await artistApi.getArtist(id)
      artist.value = mapArtistResponse(response)

      return artist.value
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isArtistLoading.value = false
    }
  }

  async function getArtistTracks(
    id: string,
    options?: { append?: boolean; fallbackArtist?: string }
  ): Promise<void> {
    const append = Boolean(options?.append)

    if (append) {
      if (!artistTracksCursor.value || isArtistTracksLoadingMore.value) {
        return
      }
      isArtistTracksLoadingMore.value = true
    } else {
      isArtistTracksLoading.value = true
      artistTracks.value = []
      artistTracksCursor.value = null
    }

    try {
      const response = await artistApi.getArtistTracks(
        id,
        append ? artistTracksCursor.value : null
      )
      const tracks = mapTracksResponse(response, options?.fallbackArtist ?? artist.value?.name ?? '')

      artistTracks.value = append ? [...artistTracks.value, ...tracks] : tracks
      artistTracksCursor.value = extractCursorFromLink(response.links?.next)
    } catch (error) {
      handleApiError(error)
    } finally {
      isArtistTracksLoading.value = false
      isArtistTracksLoadingMore.value = false
    }
  }

  async function getArtists(options?: { append?: boolean; name?: string }): Promise<void> {
    const append = Boolean(options?.append)

    if (options && 'name' in options) {
      artistListName.value = options.name?.trim() ?? ''
    }

    if (append) {
      if (!artistsCursor.value || isArtistsLoadingMore.value || isArtistsLoading.value) {
        return
      }
      isArtistsLoadingMore.value = true
    } else {
      artistListRequestId += 1
      isArtistsLoading.value = true
      isArtistsLoadingMore.value = false
      artists.value = []
      artistsCursor.value = null
      hasMoreArtists.value = false
    }

    const requestId = artistListRequestId

    try {
      const response = await artistApi.getArtists({
        name: artistListName.value || undefined,
        cursor: append ? artistsCursor.value : null
      })

      if (requestId !== artistListRequestId) {
        return
      }

      const mapped = mapArtistsResponse(response)
      artists.value = append ? mergeById(artists.value, mapped) : mapped
      artistsCursor.value = extractCursorFromResponse(response)
      hasMoreArtists.value = hasMoreFromResponse(response)
    } catch (error) {
      if (requestId !== artistListRequestId) {
        return
      }
      handleApiError(error)
    } finally {
      if (requestId === artistListRequestId) {
        isArtistsLoading.value = false
        isArtistsLoadingMore.value = false
      }
    }
  }

  return {
    artist,
    artistTracks,
    artistTracksCursor,
    isArtistLoading,
    isArtistTracksLoading,
    isArtistTracksLoadingMore,
    artists,
    artistsCursor,
    hasMoreArtists,
    isArtistsLoading,
    isArtistsLoadingMore,
    artistListName,
    getArtist,
    getArtistTracks,
    getArtists
  }
})
