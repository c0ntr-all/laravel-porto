import { defineStore } from 'pinia'
import { ref } from 'vue'
import { artistApi } from 'src/api/requests/artistApi'
import { trackApi } from 'src/api/requests/trackApi'
import { mapArtistResponse, mapArtistsResponse } from 'src/api/mappers/Music/artist.mapper'
import { mapTracksResponse } from 'src/api/mappers/Music/track.mapper'
import {
  extractCursorFromLink,
  extractCursorFromResponse,
  handleApiError,
  hasMoreFromResponse
} from 'src/utils/jsonapi'
import { IArtist, ITrack } from 'src/types'

export type TrackSortField = 'created_at' | 'name' | 'rate'

function mergeById<T extends { id: string }>(current: T[], incoming: T[]): T[] {
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
  const artistListTags = ref<string[]>([])
  const artistListTagsMatch = ref<'and' | 'or'>('or')
  const artistListTagsNested = ref(true)

  const tracks = ref<ITrack[]>([])
  const tracksCursor = ref<string | null>(null)
  const hasMoreTracks = ref(false)
  const isTracksLoading = ref(false)
  const isTracksLoadingMore = ref(false)
  const trackListName = ref('')
  const trackListTags = ref<string[]>([])
  const trackListTagsMatch = ref<'and' | 'or'>('or')
  const trackListTagsNested = ref(true)
  const trackListRates = ref<number[]>([])
  const trackListSortField = ref<TrackSortField>('created_at')
  const trackListSortDesc = ref(true)

  let artistListRequestId = 0
  let trackListRequestId = 0

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
      const mapped = mapTracksResponse(response, options?.fallbackArtist ?? artist.value?.name ?? '')

      artistTracks.value = append ? [...artistTracks.value, ...mapped] : mapped
      artistTracksCursor.value = extractCursorFromLink(response.links?.next)
    } catch (error) {
      handleApiError(error)
    } finally {
      isArtistTracksLoading.value = false
      isArtistTracksLoadingMore.value = false
    }
  }

  async function getArtists(options?: {
    append?: boolean
    name?: string
    tags?: string[]
    tagsMatch?: 'and' | 'or'
    tagsNested?: boolean
  }): Promise<void> {
    const append = Boolean(options?.append)

    if (options && 'name' in options) {
      artistListName.value = options.name?.trim() ?? ''
    }

    if (options && 'tags' in options) {
      artistListTags.value = [...(options.tags ?? [])]
    }

    if (options?.tagsMatch) {
      artistListTagsMatch.value = options.tagsMatch
    }

    if (options && 'tagsNested' in options) {
      artistListTagsNested.value = Boolean(options.tagsNested)
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
        tags: artistListTags.value,
        tags_match: artistListTagsMatch.value,
        tags_nested: artistListTagsNested.value,
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

  async function getTracks(options?: {
    append?: boolean
    name?: string
    tags?: string[]
    tagsMatch?: 'and' | 'or'
    tagsNested?: boolean
    rates?: number[]
    sortField?: TrackSortField
    sortDesc?: boolean
  }): Promise<void> {
    const append = Boolean(options?.append)

    if (options && 'name' in options) {
      trackListName.value = options.name?.trim() ?? ''
    }

    if (options && 'tags' in options) {
      trackListTags.value = [...(options.tags ?? [])]
    }

    if (options?.tagsMatch) {
      trackListTagsMatch.value = options.tagsMatch
    }

    if (options && 'tagsNested' in options) {
      trackListTagsNested.value = Boolean(options.tagsNested)
    }

    if (options && 'rates' in options) {
      trackListRates.value = [...(options.rates ?? [])]
    }

    if (options?.sortField) {
      trackListSortField.value = options.sortField
    }

    if (options && 'sortDesc' in options) {
      trackListSortDesc.value = Boolean(options.sortDesc)
    }

    if (append) {
      if (!tracksCursor.value || isTracksLoadingMore.value || isTracksLoading.value) {
        return
      }
      isTracksLoadingMore.value = true
    } else {
      trackListRequestId += 1
      isTracksLoading.value = true
      isTracksLoadingMore.value = false
      tracks.value = []
      tracksCursor.value = null
      hasMoreTracks.value = false
    }

    const requestId = trackListRequestId

    try {
      const response = await trackApi.listTracks({
        name: trackListName.value || undefined,
        tags: trackListTags.value,
        tags_match: trackListTagsMatch.value,
        tags_nested: trackListTagsNested.value,
        rate: trackListRates.value,
        sort: `${trackListSortDesc.value ? '-' : ''}${trackListSortField.value}`,
        cursor: append ? tracksCursor.value : null
      })

      if (requestId !== trackListRequestId) {
        return
      }

      const mapped = mapTracksResponse(response)
      tracks.value = append ? mergeById(tracks.value, mapped) : mapped
      tracksCursor.value = extractCursorFromResponse(response)
      hasMoreTracks.value = hasMoreFromResponse(response)
    } catch (error) {
      if (requestId !== trackListRequestId) {
        return
      }
      handleApiError(error)
    } finally {
      if (requestId === trackListRequestId) {
        isTracksLoading.value = false
        isTracksLoadingMore.value = false
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
    artistListTags,
    artistListTagsMatch,
    artistListTagsNested,
    tracks,
    tracksCursor,
    hasMoreTracks,
    isTracksLoading,
    isTracksLoadingMore,
    trackListName,
    trackListTags,
    trackListTagsMatch,
    trackListTagsNested,
    trackListRates,
    trackListSortField,
    trackListSortDesc,
    getArtist,
    getArtistTracks,
    getArtists,
    getTracks
  }
})
