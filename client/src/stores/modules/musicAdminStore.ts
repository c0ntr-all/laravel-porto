import { defineStore } from 'pinia'
import { ref } from 'vue'
import { artistApi } from 'src/api/requests/artistApi'
import { albumApi } from 'src/api/requests/albumApi'
import { trackApi } from 'src/api/requests/trackApi'
import { mapArtistResponse, mapArtistsResponse } from 'src/api/mappers/Music/artist.mapper'
import { mapAlbumResponse, mapAlbumsResponse, mapAlbumTypesResponse } from 'src/api/mappers/Music/album.mapper'
import { mapTracksResponse } from 'src/api/mappers/Music/track.mapper'
import {
  extractCursorFromResponse,
  handleApiError,
  handleApiSuccess,
  hasMoreFromResponse
} from 'src/utils/jsonapi'
import { IAlbum, IAlbumType, IAlbumWriteDto, IArtist, ITrack } from 'src/types'

function mergeById<T extends { id: string }>(current: T[], incoming: T[]): T[] {
  const seen = new Set(current.map(item => item.id))

  return [...current, ...incoming.filter(item => !seen.has(item.id))]
}

export const useMusicAdminStore = defineStore('musicAdmin', () => {
  const artists = ref<IArtist[]>([])
  const artistsCursor = ref<string | null>(null)
  const hasMoreArtists = ref(false)
  const isArtistsLoading = ref(false)
  const isArtistsLoadingMore = ref(false)
  const isArtistSaving = ref(false)
  const artistSearch = ref('')

  const albums = ref<IAlbum[]>([])
  const albumsCursor = ref<string | null>(null)
  const hasMoreAlbums = ref(false)
  const isAlbumsLoading = ref(false)
  const isAlbumsLoadingMore = ref(false)
  const isAlbumSaving = ref(false)
  const albumNameSearch = ref('')
  const albumArtistSearch = ref('')
  const albumTypes = ref<IAlbumType[]>([])
  const isAlbumTypesLoading = ref(false)

  const tracks = ref<ITrack[]>([])
  const tracksCursor = ref<string | null>(null)
  const hasMoreTracks = ref(false)
  const isTracksLoading = ref(false)
  const isTracksLoadingMore = ref(false)
  const trackNameSearch = ref('')
  const trackArtistSearch = ref('')
  const trackAlbumSearch = ref('')

  let artistsRequestId = 0
  let albumsRequestId = 0
  let tracksRequestId = 0

  async function getArtists(options?: { append?: boolean; name?: string }): Promise<void> {
    const append = Boolean(options?.append)

    if (options && 'name' in options) {
      artistSearch.value = options.name?.trim() ?? ''
    }

    if (append) {
      if (!artistsCursor.value || isArtistsLoadingMore.value || isArtistsLoading.value) {
        return
      }
      isArtistsLoadingMore.value = true
    } else {
      artistsRequestId += 1
      isArtistsLoading.value = true
      isArtistsLoadingMore.value = false
      artists.value = []
      artistsCursor.value = null
      hasMoreArtists.value = false
    }

    const requestId = artistsRequestId

    try {
      const response = await artistApi.getArtists({
        name: artistSearch.value || undefined,
        cursor: append ? artistsCursor.value : null
      })

      if (requestId !== artistsRequestId) {
        return
      }

      const mapped = mapArtistsResponse(response)
      artists.value = append ? mergeById(artists.value, mapped) : mapped
      artistsCursor.value = extractCursorFromResponse(response)
      hasMoreArtists.value = hasMoreFromResponse(response)
    } catch (error) {
      if (requestId === artistsRequestId) {
        handleApiError(error)
      }
    } finally {
      if (requestId === artistsRequestId) {
        isArtistsLoading.value = false
        isArtistsLoadingMore.value = false
      }
    }
  }

  async function updateArtist(
    id: string,
    payload: {
      name?: string
      description?: string | null
      tags?: Array<string | number>
      image_file?: File | null
    }
  ): Promise<IArtist | null> {
    isArtistSaving.value = true

    try {
      const response = await artistApi.updateArtist(id, payload)
      const artist = mapArtistResponse(response)
      artists.value = artists.value.map(item => item.id === artist.id ? artist : item)
      handleApiSuccess(response)

      return artist
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isArtistSaving.value = false
    }
  }

  async function getAlbums(options?: {
    append?: boolean
    name?: string
    artist?: string
  }): Promise<void> {
    const append = Boolean(options?.append)

    if (options && 'name' in options) {
      albumNameSearch.value = options.name?.trim() ?? ''
    }
    if (options && 'artist' in options) {
      albumArtistSearch.value = options.artist?.trim() ?? ''
    }

    if (append) {
      if (!albumsCursor.value || isAlbumsLoadingMore.value || isAlbumsLoading.value) {
        return
      }
      isAlbumsLoadingMore.value = true
    } else {
      albumsRequestId += 1
      isAlbumsLoading.value = true
      isAlbumsLoadingMore.value = false
      albums.value = []
      albumsCursor.value = null
      hasMoreAlbums.value = false
    }

    const requestId = albumsRequestId

    try {
      const response = await albumApi.listAlbums({
        name: albumNameSearch.value || undefined,
        artist: albumArtistSearch.value || undefined,
        cursor: append ? albumsCursor.value : null
      })

      if (requestId !== albumsRequestId) {
        return
      }

      const mapped = mapAlbumsResponse(response)
      albums.value = append ? mergeById(albums.value, mapped) : mapped
      albumsCursor.value = extractCursorFromResponse(response)
      hasMoreAlbums.value = hasMoreFromResponse(response)
    } catch (error) {
      if (requestId === albumsRequestId) {
        handleApiError(error)
      }
    } finally {
      if (requestId === albumsRequestId) {
        isAlbumsLoading.value = false
        isAlbumsLoadingMore.value = false
      }
    }
  }

  async function getAlbum(id: string): Promise<IAlbum | null> {
    try {
      const response = await albumApi.getAlbum(id)

      return mapAlbumResponse(response)
    } catch (error) {
      handleApiError(error)
      return null
    }
  }

  async function updateAlbum(id: string, payload: IAlbumWriteDto): Promise<IAlbum | null> {
    isAlbumSaving.value = true

    try {
      const response = await albumApi.updateAlbum(id, payload)
      const album = mapAlbumResponse(response)
      handleApiSuccess(response)
      await getAlbums()

      return album
    } catch (error) {
      handleApiError(error)
      return null
    } finally {
      isAlbumSaving.value = false
    }
  }

  async function deleteAlbum(id: string): Promise<boolean> {
    isAlbumSaving.value = true

    try {
      const response = await albumApi.deleteAlbum(id)
      albums.value = albums.value
        .map(album => ({
          ...album,
          versions: album.versions.filter(version => version.id !== id)
        }))
        .filter(album => album.id !== id)
      handleApiSuccess(response)

      return true
    } catch (error) {
      handleApiError(error)
      return false
    } finally {
      isAlbumSaving.value = false
    }
  }

  async function getTracks(options?: {
    append?: boolean
    name?: string
    artist?: string
    album?: string
  }): Promise<void> {
    const append = Boolean(options?.append)

    if (options && 'name' in options) {
      trackNameSearch.value = options.name?.trim() ?? ''
    }
    if (options && 'artist' in options) {
      trackArtistSearch.value = options.artist?.trim() ?? ''
    }
    if (options && 'album' in options) {
      trackAlbumSearch.value = options.album?.trim() ?? ''
    }

    if (append) {
      if (!tracksCursor.value || isTracksLoadingMore.value || isTracksLoading.value) {
        return
      }
      isTracksLoadingMore.value = true
    } else {
      tracksRequestId += 1
      isTracksLoading.value = true
      isTracksLoadingMore.value = false
      tracks.value = []
      tracksCursor.value = null
      hasMoreTracks.value = false
    }

    const requestId = tracksRequestId

    try {
      const response = await trackApi.listTracks({
        name: trackNameSearch.value || undefined,
        artist: trackArtistSearch.value || undefined,
        album: trackAlbumSearch.value || undefined,
        cursor: append ? tracksCursor.value : null
      })

      if (requestId !== tracksRequestId) {
        return
      }

      const mapped = mapTracksResponse(response)
      tracks.value = append ? mergeById(tracks.value, mapped) : mapped
      tracksCursor.value = extractCursorFromResponse(response)
      hasMoreTracks.value = hasMoreFromResponse(response)
    } catch (error) {
      if (requestId === tracksRequestId) {
        handleApiError(error)
      }
    } finally {
      if (requestId === tracksRequestId) {
        isTracksLoading.value = false
        isTracksLoadingMore.value = false
      }
    }
  }

  async function loadAlbumTypes(): Promise<IAlbumType[]> {
    if (albumTypes.value.length) {
      return albumTypes.value
    }

    isAlbumTypesLoading.value = true

    try {
      const response = await albumApi.listAlbumTypes()
      albumTypes.value = mapAlbumTypesResponse(response)

      return albumTypes.value
    } catch (error) {
      handleApiError(error)
      return []
    } finally {
      isAlbumTypesLoading.value = false
    }
  }

  async function searchArtistOptions(name: string): Promise<IArtist[]> {
    try {
      const response = await artistApi.getArtists({ name: name.trim() || undefined })

      return mapArtistsResponse(response)
    } catch {
      return []
    }
  }

  async function searchAlbumOptions(
    name: string,
    artistIds?: Array<string | number>
  ): Promise<IAlbum[]> {
    const ids = (artistIds ?? [])
      .map(id => String(id))
      .filter(id => id !== '')

    if (ids.length === 0) {
      return []
    }

    try {
      const response = await albumApi.listAlbums({
        name: name.trim() || undefined,
        artist_id: ids
      })

      return mapAlbumsResponse(response)
    } catch {
      return []
    }
  }

  return {
    artists,
    artistsCursor,
    hasMoreArtists,
    isArtistsLoading,
    isArtistsLoadingMore,
    isArtistSaving,
    artistSearch,
    albums,
    albumsCursor,
    hasMoreAlbums,
    isAlbumsLoading,
    isAlbumsLoadingMore,
    isAlbumSaving,
    albumNameSearch,
    albumArtistSearch,
    albumTypes,
    isAlbumTypesLoading,
    tracks,
    tracksCursor,
    hasMoreTracks,
    isTracksLoading,
    isTracksLoadingMore,
    trackNameSearch,
    trackArtistSearch,
    trackAlbumSearch,
    getArtists,
    updateArtist,
    getAlbums,
    getAlbum,
    updateAlbum,
    deleteAlbum,
    getTracks,
    loadAlbumTypes,
    searchArtistOptions,
    searchAlbumOptions
  }
})
