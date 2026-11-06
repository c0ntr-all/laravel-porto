import { defineStore } from 'pinia'
import { ref } from 'vue'
import { artistApi } from 'src/api/requests/artistApi'
import { trackApi } from 'src/api/requests/trackApi'
import { mapArtistResponse, mapArtistsResponse } from 'src/api/mappers/Music/artist.mapper'
import { mapTracksResponse } from 'src/api/mappers/Music/track.mapper'
import { extractCursorFromLink, handleApiError } from 'src/utils/jsonapi'
import { mergeById } from 'src/utils/collection'
import { createListRequest, mergeCursorPage, withListRequest } from 'src/utils/listRequest'
import { IArtist, ITrack } from 'src/types'

export type TrackSortField = 'created_at' | 'name' | 'rate'

export const useMusicCatalogStore = defineStore('musicCatalog', () => {
  const artist = ref<IArtist | null>(null)
  const artistTracks = ref<ITrack[]>([])
  const artistTracksCursor = ref<string | null>(null)
  const isArtistLoading = ref(false)
  const isArtistTracksLoading = ref(false)
  const isArtistTracksLoadingMore = ref(false)
  const artistTrackListRates = ref<number[]>([])
  const artistTrackListSortField = ref<TrackSortField>('created_at')
  const artistTrackListSortDesc = ref(true)
  const artistTrackListRequest = createListRequest()

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

  const artistListRequest = createListRequest()
  const trackListRequest = createListRequest()

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
    options?: {
      append?: boolean
      fallbackArtist?: string
      rates?: number[]
      sortField?: TrackSortField
      sortDesc?: boolean
    }
  ): Promise<void> {
    if (!id) {
      return
    }

    const append = Boolean(options?.append)

    if (options && 'rates' in options) {
      artistTrackListRates.value = [...(options.rates ?? [])]
    }

    if (options?.sortField) {
      artistTrackListSortField.value = options.sortField
    }

    if (options && 'sortDesc' in options) {
      artistTrackListSortDesc.value = Boolean(options.sortDesc)
    }

    await withListRequest(artistTrackListRequest, append, {
      canAppend: Boolean(artistTracksCursor.value) && !isArtistTracksLoadingMore.value && !isArtistTracksLoading.value,
      onAppend: () => {
        isArtistTracksLoadingMore.value = true
      },
      onFresh: () => {
        isArtistTracksLoading.value = true
        isArtistTracksLoadingMore.value = false
        artistTracks.value = []
        artistTracksCursor.value = null
      },
      onSettle: () => {
        isArtistTracksLoading.value = false
        isArtistTracksLoadingMore.value = false
      }
    }, async (isCurrent) => {
      try {
        const response = await artistApi.getArtistTracks(id, {
          cursor: append ? artistTracksCursor.value : null,
          rate: artistTrackListRates.value,
          sort: `${artistTrackListSortDesc.value ? '-' : ''}${artistTrackListSortField.value}`
        })

        if (!isCurrent()) {
          return
        }

        const mapped = mapTracksResponse(response, options?.fallbackArtist ?? artist.value?.name ?? '')

        artistTracks.value = append ? mergeById(artistTracks.value, mapped) : mapped
        artistTracksCursor.value = extractCursorFromLink(response.links?.next)
      } catch (error) {
        if (!isCurrent()) {
          return
        }
        handleApiError(error)
      }
    })
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

    await withListRequest(artistListRequest, append, {
      canAppend: Boolean(artistsCursor.value) && !isArtistsLoadingMore.value && !isArtistsLoading.value,
      onAppend: () => {
        isArtistsLoadingMore.value = true
      },
      onFresh: () => {
        isArtistsLoading.value = true
        isArtistsLoadingMore.value = false
        artists.value = []
        artistsCursor.value = null
        hasMoreArtists.value = false
      },
      onSettle: () => {
        isArtistsLoading.value = false
        isArtistsLoadingMore.value = false
      }
    }, async (isCurrent) => {
      try {
        const response = await artistApi.getArtists({
          name: artistListName.value || undefined,
          tags: artistListTags.value,
          tags_match: artistListTagsMatch.value,
          tags_nested: artistListTagsNested.value,
          cursor: append ? artistsCursor.value : null
        })

        if (!isCurrent()) {
          return
        }

        const page = mergeCursorPage(artists.value, mapArtistsResponse(response), response, append)
        artists.value = page.items
        artistsCursor.value = page.cursor
        hasMoreArtists.value = page.hasMore
      } catch (error) {
        if (!isCurrent()) {
          return
        }
        handleApiError(error)
      }
    })
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

    await withListRequest(trackListRequest, append, {
      canAppend: Boolean(tracksCursor.value) && !isTracksLoadingMore.value && !isTracksLoading.value,
      onAppend: () => {
        isTracksLoadingMore.value = true
      },
      onFresh: () => {
        isTracksLoading.value = true
        isTracksLoadingMore.value = false
        tracks.value = []
        tracksCursor.value = null
        hasMoreTracks.value = false
      },
      onSettle: () => {
        isTracksLoading.value = false
        isTracksLoadingMore.value = false
      }
    }, async (isCurrent) => {
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

        if (!isCurrent()) {
          return
        }

        const page = mergeCursorPage(tracks.value, mapTracksResponse(response), response, append)
        tracks.value = page.items
        tracksCursor.value = page.cursor
        hasMoreTracks.value = page.hasMore
      } catch (error) {
        if (!isCurrent()) {
          return
        }
        handleApiError(error)
      }
    })
  }

  return {
    artist,
    artistTracks,
    artistTracksCursor,
    isArtistLoading,
    isArtistTracksLoading,
    isArtistTracksLoadingMore,
    artistTrackListRates,
    artistTrackListSortField,
    artistTrackListSortDesc,
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
