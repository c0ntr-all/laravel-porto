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
import { handleApiError } from 'src/utils/jsonapi'
import { createListRequest, mergeCursorPage, withListRequest } from 'src/utils/listRequest'
import { MovieTypeEnum } from 'src/enums/Movie/MovieTypeEnum'
import { IMovie, IMovieCredit, IMovieFolder, IMovieSeason } from 'src/types/Movie'
import { withFolderMembership } from 'src/utils/movieFolders'
import {
  replaceEpisode,
  replaceSeason,
  withEpisodeWatched,
  withSeasonWatched
} from 'src/utils/movieSeasons'

export const useMovieStore = defineStore('movies', () => {
  const movies = ref<IMovie[]>([])
  const movie = ref<IMovie | null>(null)
  const movieCredits = ref<IMovieCredit[]>([])
  const moviesCursor = ref<string | null>(null)
  const moviesTotal = ref<number | null>(null)
  const hasMoreMovies = ref(false)
  const isMoviesLoading = ref(false)
  const isMoviesLoadingMore = ref(false)
  const isMovieLoading = ref(false)
  const isMovieCreditsLoading = ref(false)
  const listTitle = ref('')
  const listType = ref<MovieTypeEnum | null>(null)
  const listGenreIds = ref<string[]>([])
  const listYears = ref<number[]>([])
  const listSort = ref<string | null>(null)
  const pendingWatchKeys = ref<string[]>([])

  const listRequest = createListRequest()
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
    genre_ids?: string[]
    years?: number[]
    sort?: string | null
  }): Promise<void> {
    const append = Boolean(options?.append)

    if (options && 'title' in options) {
      listTitle.value = options.title?.trim() ?? ''
    }

    if (options && 'type' in options) {
      listType.value = options.type ?? null
    }

    if (options && 'genre_ids' in options) {
      listGenreIds.value = [...(options.genre_ids ?? [])]
    }

    if (options && 'years' in options) {
      listYears.value = [...(options.years ?? [])]
    }

    if (options && 'sort' in options) {
      listSort.value = options.sort || null
    }

    await withListRequest(listRequest, append, {
      canAppend: Boolean(moviesCursor.value) && !isMoviesLoadingMore.value && !isMoviesLoading.value,
      onAppend: () => {
        isMoviesLoadingMore.value = true
      },
      onFresh: () => {
        isMoviesLoading.value = true
        isMoviesLoadingMore.value = false
        movies.value = []
        moviesCursor.value = null
        hasMoreMovies.value = false
      },
      onSettle: () => {
        isMoviesLoading.value = false
        isMoviesLoadingMore.value = false
      }
    }, async (isCurrent) => {
      try {
        const response = await movieApi.getMovies({
          title: listTitle.value || undefined,
          type: listType.value ?? undefined,
          genre_id: listGenreIds.value.length ? listGenreIds.value : undefined,
          year: listYears.value.length ? listYears.value : undefined,
          sort: listSort.value || undefined,
          cursor: append ? moviesCursor.value : null
        })

        if (!isCurrent()) {
          return
        }

        const page = mergeCursorPage(movies.value, mapMoviesResponse(response), response, append)
        movies.value = page.items
        moviesCursor.value = page.cursor
        hasMoreMovies.value = page.hasMore

        if (typeof response.meta?.total === 'number') {
          moviesTotal.value = response.meta.total
        }
      } catch (error) {
        if (!isCurrent()) {
          return
        }

        handleApiError(error)
      }
    })
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
    moviesTotal,
    hasMoreMovies,
    isMoviesLoading,
    isMoviesLoadingMore,
    isMovieLoading,
    isMovieCreditsLoading,
    listTitle,
    listType,
    listGenreIds,
    listYears,
    listSort,
    isWatchPending,
    getMovies,
    getMovie,
    getMovieCredits,
    setMovieFolderMembership,
    toggleSeasonWatched,
    toggleEpisodeWatched
  }
})
