import { defineStore } from 'pinia'
import { ref } from 'vue'
import { movieApi } from 'src/api/requests/movieApi'
import {
  mapMovieCreditsResponse,
  mapMovieEpisodeResponse,
  mapMovieResponse,
  mapMovieSeasonResponse,
  mapMoviesResponse
} from 'src/api/mappers/Movie/movie.mapper'
import { extractCursorFromResponse, handleApiError, hasMoreFromResponse } from 'src/utils/jsonapi'
import { MovieTypeEnum } from 'src/enums/Movie/MovieTypeEnum'
import { IMovie, IMovieCredit, IMovieFolder, IMovieSeason } from 'src/types/Movie'
import { withFolderMembership } from 'src/utils/movieFolders'
import {
  replaceEpisode,
  replaceSeason,
  withEpisodeWatched,
  withSeasonWatched
} from 'src/utils/movieSeasons'

function mergeById(current: IMovie[], incoming: IMovie[]): IMovie[] {
  const seen = new Set(current.map(item => item.id))

  return [...current, ...incoming.filter(item => !seen.has(item.id))]
}

export const useMovieStore = defineStore('movies', () => {
  const movies = ref<IMovie[]>([])
  const movie = ref<IMovie | null>(null)
  const movieCredits = ref<IMovieCredit[]>([])
  const moviesCursor = ref<string | null>(null)
  const hasMoreMovies = ref(false)
  const isMoviesLoading = ref(false)
  const isMoviesLoadingMore = ref(false)
  const isMovieLoading = ref(false)
  const isMovieCreditsLoading = ref(false)
  const listTitle = ref('')
  const listType = ref<MovieTypeEnum | null>(null)
  const pendingWatchKeys = ref<string[]>([])

  let listRequestId = 0
  let creditsMovieId: string | null = null

  function isWatchPending(key: string): boolean {
    return pendingWatchKeys.value.includes(key)
  }

  function patchMovieSeasons(seasons: IMovieSeason[]): void {
    if (!movie.value) {
      return
    }

    movie.value = {
      ...movie.value,
      seasons
    }
  }

  async function getMovies(options?: {
    append?: boolean
    title?: string
    type?: MovieTypeEnum | null
  }): Promise<void> {
    const append = Boolean(options?.append)

    if (options && 'title' in options) {
      listTitle.value = options.title?.trim() ?? ''
    }

    if (options && 'type' in options) {
      listType.value = options.type ?? null
    }

    if (append) {
      if (!moviesCursor.value || isMoviesLoadingMore.value || isMoviesLoading.value) {
        return
      }

      isMoviesLoadingMore.value = true
    } else {
      listRequestId += 1
      isMoviesLoading.value = true
      isMoviesLoadingMore.value = false
      movies.value = []
      moviesCursor.value = null
      hasMoreMovies.value = false
    }

    const requestId = listRequestId

    try {
      const response = await movieApi.getMovies({
        title: listTitle.value || undefined,
        type: listType.value ?? undefined,
        cursor: append ? moviesCursor.value : null
      })

      if (requestId !== listRequestId) {
        return
      }

      const mapped = mapMoviesResponse(response)
      movies.value = append ? mergeById(movies.value, mapped) : mapped
      moviesCursor.value = extractCursorFromResponse(response)
      hasMoreMovies.value = hasMoreFromResponse(response)
    } catch (error) {
      if (requestId !== listRequestId) {
        return
      }

      handleApiError(error)
    } finally {
      if (requestId === listRequestId) {
        isMoviesLoading.value = false
        isMoviesLoadingMore.value = false
      }
    }
  }

  async function getMovie(id: string): Promise<IMovie | null> {
    const listed = movies.value.find(item => item.id === id)

    if (movie.value?.id !== id) {
      movie.value = listed ?? null
    }

    isMovieLoading.value = true

    try {
      const response = await movieApi.getMovie(id)
      movie.value = mapMovieResponse(response)

      return movie.value
    } catch (error) {
      handleApiError(error)
      return listed ?? null
    } finally {
      isMovieLoading.value = false
    }
  }

  async function getMovieCredits(id: string): Promise<IMovieCredit[]> {
    if (creditsMovieId !== id) {
      movieCredits.value = []
      creditsMovieId = id
    }

    isMovieCreditsLoading.value = true

    try {
      const response = await movieApi.getMovieCredits(id)
      movieCredits.value = mapMovieCreditsResponse(response)

      return movieCredits.value
    } catch (error) {
      handleApiError(error)
      return []
    } finally {
      isMovieCreditsLoading.value = false
    }
  }

  function setMovieFolderMembership(movieId: string, folder: IMovieFolder, present: boolean): void {
    movies.value = movies.value.map(item => (
      item.id === movieId ? withFolderMembership(item, folder, present) : item
    ))

    if (movie.value?.id === movieId) {
      movie.value = withFolderMembership(movie.value, folder, present)
    }
  }

  async function toggleSeasonWatched(season: IMovieSeason): Promise<void> {
    const key = `season:${season.id}`

    if (!movie.value || isWatchPending(key)) {
      return
    }

    const watched = !season.is_watched
    const previous = movie.value.seasons
    pendingWatchKeys.value = [...pendingWatchKeys.value, key]
    patchMovieSeasons(replaceSeason(previous, withSeasonWatched(season, watched)))

    try {
      const response = watched
        ? await movieApi.markSeasonWatched(season.id)
        : await movieApi.unmarkSeasonWatched(season.id)
      const mapped = mapMovieSeasonResponse(response)

      patchMovieSeasons(replaceSeason(
        movie.value?.seasons ?? [],
        withSeasonWatched(mapped, watched)
      ))
    } catch (error) {
      patchMovieSeasons(previous)
      handleApiError(error)
    } finally {
      pendingWatchKeys.value = pendingWatchKeys.value.filter(item => item !== key)
    }
  }

  async function toggleEpisodeWatched(season: IMovieSeason, episodeId: string): Promise<void> {
    const key = `episode:${episodeId}`
    const episode = season.episodes.find(item => item.id === episodeId)

    if (!movie.value || !episode || isWatchPending(key)) {
      return
    }

    const watched = !episode.is_watched
    const previous = movie.value.seasons
    pendingWatchKeys.value = [...pendingWatchKeys.value, key]
    patchMovieSeasons(replaceSeason(previous, withEpisodeWatched(season, episodeId, watched)))

    try {
      const response = watched
        ? await movieApi.markEpisodeWatched(episodeId)
        : await movieApi.unmarkEpisodeWatched(episodeId)
      const mapped = mapMovieEpisodeResponse(response)

      patchMovieSeasons(replaceEpisode(movie.value?.seasons ?? [], {
        ...mapped,
        is_watched: watched
      }))
    } catch (error) {
      patchMovieSeasons(previous)
      handleApiError(error)
    } finally {
      pendingWatchKeys.value = pendingWatchKeys.value.filter(item => item !== key)
    }
  }

  return {
    movies,
    movie,
    movieCredits,
    moviesCursor,
    hasMoreMovies,
    isMoviesLoading,
    isMoviesLoadingMore,
    isMovieLoading,
    isMovieCreditsLoading,
    listTitle,
    listType,
    isWatchPending,
    getMovies,
    getMovie,
    getMovieCredits,
    setMovieFolderMembership,
    toggleSeasonWatched,
    toggleEpisodeWatched
  }
})
