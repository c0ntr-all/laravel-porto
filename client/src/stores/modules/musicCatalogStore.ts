import { defineStore } from 'pinia'
import { ref } from 'vue'
import { artistApi } from 'src/api/requests/artistApi'
import { mapArtistResponse } from 'src/api/mappers/Music/artist.mapper'
import { mapTracksResponse } from 'src/api/mappers/Music/track.mapper'
import { extractCursorFromLink, handleApiError } from 'src/utils/jsonapi'
import { IArtist, ITrack } from 'src/types'

export const useMusicCatalogStore = defineStore('musicCatalog', () => {
  const artist = ref<IArtist | null>(null)
  const artistTracks = ref<ITrack[]>([])
  const artistTracksCursor = ref<string | null>(null)
  const isArtistLoading = ref(false)
  const isArtistTracksLoading = ref(false)
  const isArtistTracksLoadingMore = ref(false)

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

  return {
    artist,
    artistTracks,
    artistTracksCursor,
    isArtistLoading,
    isArtistTracksLoading,
    isArtistTracksLoadingMore,
    getArtist,
    getArtistTracks
  }
})
